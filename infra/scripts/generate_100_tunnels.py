#!/usr/bin/env python3
"""
100-Tunnel Speed Aggregator Configuration Generator (Dynamic CLI Version)
Author: Antigravity

This script generates a high-speed, parallelized VLESS-over-XHTTP reverse tunnel configuration
with N concurrent paths. It creates:
1. bridge_100_tunnels.json - To be deployed on the Bridge side (srv10 / outside server)
2. portal_100_tunnels.json - To be deployed on the Portal side (srv01 / inside server)

Traffic entering the Portal's VLESS proxy is dynamically load-balanced across all active
reverse connections, bypassing standard single-stream TCP limitations and GFW throttles.
"""

import json
import os
import argparse
import uuid
import datetime
from cryptography import x509
from cryptography.hazmat.primitives import hashes, serialization
from cryptography.hazmat.primitives.asymmetric import rsa
from cryptography.x509.oid import NameOID

# Common Credentials
UUID_NAMESPACE = uuid.NAMESPACE_DNS

def generate_new_tls_cert(domain):
    """Generates a valid X.509 certificate and returns it in the format Xray/Marzban expects."""
    key = rsa.generate_private_key(public_exponent=65537, key_size=2048)
    subject = issuer = x509.Name([x509.NameAttribute(NameOID.COMMON_NAME, domain)])
    cert = (
        x509.CertificateBuilder()
        .subject_name(subject)
        .issuer_name(issuer)
        .public_key(key.public_key())
        .serial_number(x509.random_serial_number())
        .not_valid_before(datetime.datetime.utcnow())
        .not_valid_after(datetime.datetime.utcnow() + datetime.timedelta(days=3650))
        .add_extension(x509.SubjectAlternativeName([x509.DNSName(domain)]), critical=False)
        .sign(key, hashes.SHA256())
    )
    
    key_pem = key.private_bytes(
        encoding=serialization.Encoding.PEM,
        format=serialization.PrivateFormat.TraditionalOpenSSL,
        encryption_algorithm=serialization.NoEncryption()
    ).decode('utf-8')
    cert_pem = cert.public_bytes(serialization.Encoding.PEM).decode('utf-8')
    
    # Return as single-element lists containing the entire PEM string (including newlines).
    # This prevents Marzban's strict parsing from encountering InvalidPadding or InvalidLastSymbol
    # caused by concatenating array elements without newlines.
    return [cert_pem], [key_pem]

def generate_bridge_config(domain, path, key, count, port=443, bridge_tag_mode="unified", dialer_proxy="tor", uuid_map=None):
    outbounds = [
        {
            "protocol": "freedom",
            "tag": "direct"
        }
    ]
    
    inbound_tags = []
    
    # Generate parallel VLESS bridge outbounds
    for i in range(1, count + 1):
        tunnel_tag = f"tunnel_{i:03d}"
        bridge_tag = "reverse-bridge" if bridge_tag_mode == "unified" else f"bridge_{i:03d}"
        if bridge_tag not in inbound_tags:
            inbound_tags.append(bridge_tag)
        
        email = f"tunnel_{key}_{i:03d}@reverse"
        if uuid_map and email in uuid_map:
            tunnel_uuid = uuid_map[email]
        else:
            tunnel_uuid = str(uuid.uuid5(UUID_NAMESPACE, f"tunnel_{key}_{i:03d}"))
            
        vless_outbound = {
            "protocol": "vless",
            "tag": tunnel_tag,
            "settings": {
                "address": domain,
                "port": port,
                "id": tunnel_uuid,
                "email": email,
                "encryption": "none",
                "reverse": {
                    "tag": bridge_tag
                }
            },
            "streamSettings": {
                "network": "xhttp",
                "security": "tls",
                "tlsSettings": {
                    "allowInsecure": True,
                    "serverName": domain,
                    "alpn": [
                        "h2",
                        "http/1.1"
                    ]
                },
                "xhttpSettings": {
                    "path": path,
                    "mode": "packet-up",
                    "extra": {
                        "xmux": {
                            "maxConcurrency": 1000,
                            "maxConnections": 0,
                            "cMaxReuseTimes": 0,
                            "hMaxRequestTimes": 1000,
                            "hMaxReusableSecs": 90,
                            "hKeepAlivePeriod": 15
                        },
                        "xPaddingPlacement": "header",
                        "xPaddingHeader": "X-Padding",
                        "xPaddingBytes": "500-1500",
                        "xPaddingObfsMode": True
                    }
                },
                "sockopt": {
                    "domainStrategy": "AsIs",
                    "tcpKeepAliveIdle": 30,
                    "tcpKeepAliveInterval": 15
                }
            }
        }
        
        if dialer_proxy:
            vless_outbound["streamSettings"]["sockopt"]["dialerProxy"] = dialer_proxy
            
        outbounds.append(vless_outbound)
        
    # Add dialer proxy outbound if specified
    if dialer_proxy == "tor":
        outbounds.append({
            "tag": "tor",
            "protocol": "socks",
            "settings": {
                "servers": [
                    {
                        "address": "127.0.0.1",
                        "port": 10110
                    }
                ]
            }
        })
    elif dialer_proxy:
        outbounds.append({
            "tag": dialer_proxy,
            "protocol": "socks",
            "settings": {
                "servers": [
                    {
                        "address": "127.0.0.1",
                        "port": 1080
                    }
                ]
            }
        })
    
    routing_rules = [
        {
            "type": "field",
            "inboundTag": inbound_tags,
            "outboundTag": "direct"
        }
    ]
    
    config = {
        "log": {
            "loglevel": "debug"
        },
        "inbounds": [],
        "outbounds": outbounds,
        "routing": {
            "domainStrategy": "AsIs",
            "rules": routing_rules
        }
    }
    return config

def generate_portal_config(path, key, count, port, probe_url, probe_interval, cert_data, key_data, uuid_map=None):
    clients = []
    reverse_out_tags = []
    
    # Generate Portal clients
    for i in range(1, count + 1):
        outbound_tag = f"reverse-out-{i:03d}"
        reverse_out_tags.append(outbound_tag)
        
        email = f"tunnel_{key}_{i:03d}@reverse"
        if uuid_map and email in uuid_map:
            tunnel_uuid = uuid_map[email]
        else:
            tunnel_uuid = str(uuid.uuid5(UUID_NAMESPACE, f"tunnel_{key}_{i:03d}"))
            
        clients.append({
            "id": tunnel_uuid,
            "email": email,
            "reverse": {
                "tag": outbound_tag
            }
        })
        
    inbounds = [
        # VLESS Reverse Portal Listener receiving the connections
        {
            "tag": "IN_REVERSE_PORTAL_100",
            "port": port,
            "listen": "0.0.0.0",
            "protocol": "vless",
            "settings": {
                "clients": clients,
                "decryption": "none"
            },
            "streamSettings": {
                "network": "xhttp",
                "security": "tls",
                "tlsSettings": {
                    "certificates": [
                        {
                            "certificate": cert_data,
                            "key": key_data
                        }
                    ],
                    "alpn": [
                        "h2",
                        "http/1.1"
                    ]
                },
                "xhttpSettings": {
                    "path": path,
                    "mode": "packet-up",
                    "extra": {
                        "xPaddingPlacement": "header",
                        "xPaddingHeader": "X-Padding",
                        "xPaddingBytes": "500-1500",
                        "xPaddingObfsMode": True
                    }
                }
            }
        }
    ]
    
    # Load balancer to distribute traffic evenly across the reverse outbounds
    balancers = [
        {
            "tag": "balancer_100",
            "selector": [
                "reverse-out-"
            ],
            "strategy": {
                "type": "leastPing"
            },
            "fallbackTag": "direct"
        }
    ]
    
    routing_rules = [
        {
            "type": "field",
            "inboundTag": [
                "IN_REVERSE_PORTAL_100"
            ],
            "balancerTag": "balancer_100"
        },
        {
            "type": "field",
            "port": "0-65535",
            "outboundTag": "direct"
        }
    ]
    
    config = {
        "log": {
            "loglevel": "debug"
        },
        "observatory": {
            "subjectSelector": [
                "reverse-out-"
            ],
            "probeUrl": probe_url,
            "probeInterval": probe_interval
        },
        "inbounds": inbounds,
        "outbounds": [
            {
                "protocol": "freedom",
                "tag": "direct"
            },
            {
                "protocol": "blackhole",
                "tag": "block"
            }
        ],
        "routing": {
            "domainStrategy": "AsIs",
            "balancers": balancers,
            "rules": routing_rules
        }
    }
    return config

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="100-Tunnel SPEED AGGREGATOR Generator")
    parser.add_argument("--domain", type=str, default="i-01.doctel.ir", help="CDN endpoint domain")
    parser.add_argument("--path", type=str, default="/", help="XHTTP request path")
    parser.add_argument("--port", type=int, default=443, help="CDN-supported port for the tunnel (e.g., 443, 2053, 8443)")
    parser.add_argument("--key", type=str, default="100_10_01_05", help="Key prefix for VLESS emails")
    parser.add_argument("--count", type=int, default=100, help="Number of concurrent tunnels")
    parser.add_argument("--outdir", type=str, default="configs/xray/generated", help="Output directory")
    parser.add_argument("--bridge-tag-mode", type=str, choices=["unified", "unique"], default="unified", help="Bridge reverse tag mode (unified or unique)")
    parser.add_argument("--dialer-proxy", type=str, default="", help="Dialer proxy outbound tag (e.g. tor, socks)")
    parser.add_argument("--probe-url", type=str, default="https://www.google.com/generate_204", help="Observatory probe URL")
    parser.add_argument("--probe-interval", type=str, default="5s", help="Observatory probe interval")
    
    args = parser.parse_args()
    
    out_dir = os.path.abspath(args.outdir)
    os.makedirs(out_dir, exist_ok=True)
    
    print(f"[*] Configuration parameters:")
    print(f"    - Domain:          {args.domain}")
    print(f"    - Path:            {args.path}")
    print(f"    - Port:            {args.port}")
    print(f"    - Key:             {args.key}")
    print(f"    - Count:           {args.count}")
    print(f"    - Bridge Tag Mode: {args.bridge_tag_mode}")
    print(f"    - Dialer Proxy:    {args.dialer_proxy if args.dialer_proxy else 'None (Direct connection)'}")
    print(f"    - Probe URL:       {args.probe_url}")
    print(f"    - Probe Interval:  {args.probe_interval}")
    
    # Parse and preserve existing client UUIDs and SSL certificates if the portal file exists
    existing_uuids = {}
    existing_certs = None
    existing_key = None
    portal_file = os.path.join(out_dir, f"portal_100_tunnels_{args.key}.json")
    if os.path.exists(portal_file):
        try:
            with open(portal_file, "r", encoding="utf-8") as f:
                old_cfg = json.load(f)
                for inbound in old_cfg.get("inbounds", []):
                    if inbound.get("tag") == "IN_REVERSE_PORTAL_100":
                        # Extract certs
                        old_certs = inbound.get("streamSettings", {}).get("tlsSettings", {}).get("certificates", [])
                        if old_certs and len(old_certs) > 0:
                            existing_certs = old_certs[0].get("certificate")
                            existing_key = old_certs[0].get("key")
                            if existing_certs and existing_key:
                                print(f"[*] Found and preserving existing SSL certificate from: {portal_file}")
                        # Extract clients
                        old_clients = inbound.get("settings", {}).get("clients", [])
                        for client in old_clients:
                            email = client.get("email")
                            client_id = client.get("id")
                            if email and client_id:
                                existing_uuids[email] = client_id
                        if existing_uuids:
                            print(f"[*] Found and preserving {len(existing_uuids)} client UUIDs from: {portal_file}")
                        break
        except Exception as e:
            print(f"[!] Warning: Could not parse existing portal config: {e}")

    # Generate Bridge Config
    bridge_cfg = generate_bridge_config(args.domain, args.path, args.key, args.count, args.port, args.bridge_tag_mode, args.dialer_proxy, existing_uuids)
    bridge_file = os.path.join(out_dir, f"bridge_100_tunnels_{args.key}.json")
    print(f"[*] Writing Bridge config to: {bridge_file}")
    with open(bridge_file, "w", encoding="utf-8") as f:
        json.dump(bridge_cfg, f, indent=2)
        
    # Generate/Restore TLS Cert
    if existing_certs and existing_key:
        cert_data, key_data = existing_certs, existing_key
    else:
        print("[*] Generating programmatic X.509 RSA Certificate...")
        cert_data, key_data = generate_new_tls_cert(args.domain)

    # Generate Portal Config
    portal_cfg = generate_portal_config(args.path, args.key, args.count, args.port, args.probe_url, args.probe_interval, cert_data, key_data, existing_uuids)
    print(f"[*] Writing Portal config to: {portal_file}")
    with open(portal_file, "w", encoding="utf-8") as f:
        json.dump(portal_cfg, f, indent=2)
        
    print("[*] Successfully generated parallel tunnel configurations!")