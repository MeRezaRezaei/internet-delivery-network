#!/usr/bin/env python3
"""
100-Tunnel Speed Aggregator Configuration Generator (Dynamic CLI Version)
Author: Antigravity

This script generates a high-speed, parallelized VLESS-over-XHTTP reverse tunnel configuration
with N concurrent paths. It creates:
1. bridge_100_tunnels.json - To be deployed on the Bridge side (srv10 / outside server)
2. portal_100_tunnels.json - To be deployed on the Portal side (srv01 / inside server)

Traffic entering the Portal's SOCKS proxy is dynamically load-balanced across all active
reverse connections, bypassing standard single-stream TCP limitations and GFW throttles.
"""

import json
import os
import argparse

# Common Credentials
UUID = "58764c09-99c3-4496-9591-9cff83e4c7b7"
PORTAL_LISTEN_PORT = 15100
PORTAL_SOCKS_PORT = 10800

def generate_bridge_config(domain, path, key, count):
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
        bridge_tag = f"bridge_{i:03d}"
        inbound_tags.append(bridge_tag)
        
        outbounds.append({
            "protocol": "vless",
            "tag": tunnel_tag,
            "settings": {
                "address": domain,
                "port": 443,
                "id": UUID,
                "email": f"tunnel_{key}_{i:03d}@reverse",
                "encryption": "none",
                "reverse": {
                    "tag": bridge_tag
                }
            },
            "streamSettings": {
                "network": "xhttp",
                "security": "tls",
                "xhttpSettings": {
                    "path": path,
                    "mode": "packet-up"
                },
                "sockopt": {
                    "dialerProxy": "tor",
                    "domainStrategy": "AsIs",
                    "tcpKeepAliveIdle": 30,
                    "tcpKeepAliveInterval": 15
                }
            }
        })
        
    # Add Tor Socks proxy outbound
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
    
    routing_rules = [
        {
            "type": "field",
            "inboundTag": inbound_tags,
            "outboundTag": "direct"
        }
    ]
    
    config = {
        "log": {
            "loglevel": "warning"
        },
        "inbounds": [],
        "outbounds": outbounds,
        "routing": {
            "domainStrategy": "AsIs",
            "rules": routing_rules
        }
    }
    return config

def generate_portal_config(path, key, count):
    clients = []
    reverse_out_tags = []
    
    # Generate Portal clients
    for i in range(1, count + 1):
        outbound_tag = f"reverse-out-{i:03d}"
        reverse_out_tags.append(outbound_tag)
        
        clients.append({
            "id": UUID,
            "email": f"tunnel_{key}_{i:03d}@reverse",
            "reverse": {
                "tag": outbound_tag
            }
        })
        
    inbounds = [
        # 1. Local Entrypoint SOCKS Inbound for traffic routing/testing
        {
            "tag": "socks-in",
            "port": PORTAL_SOCKS_PORT,
            "listen": "127.0.0.1",
            "protocol": "socks",
            "settings": {
                "auth": "noauth",
                "udp": True
            }
        },
        # 2. VLESS Reverse Portal Listener receiving the connections
        {
            "tag": "IN_REVERSE_PORTAL_100",
            "port": PORTAL_LISTEN_PORT,
            "listen": "0.0.0.0",
            "protocol": "vless",
            "settings": {
                "clients": clients,
                "decryption": "none"
            },
            "streamSettings": {
                "network": "xhttp",
                "xhttpSettings": {
                    "path": path,
                    "mode": "auto"
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
            "strategy": "random"
        }
    ]
    
    routing_rules = [
        # Route socks-in traffic into the load balancer
        {
            "type": "field",
            "inboundTag": ["socks-in"],
            "balancerTag": "balancer_100"
        },
        # Route established reverse tunnel traffic directly to the Internet
        {
            "type": "field",
            "inboundTag": reverse_out_tags,
            "outboundTag": "direct"
        },
        # Block anything else
        {
            "type": "field",
            "port": "0-65535",
            "outboundTag": "block"
        }
    ]
    
    config = {
        "log": {
            "loglevel": "warning"
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
    parser.add_argument("--path", type=str, default="/100-10-01-05", help="XHTTP request path")
    parser.add_argument("--key", type=str, default="100_10_01_05", help="Key prefix for VLESS emails")
    parser.add_argument("--count", type=int, default=100, help="Number of concurrent tunnels")
    parser.add_argument("--outdir", type=str, default="configs/xray/generated", help="Output directory")
    
    args = parser.parse_args()
    
    out_dir = os.path.abspath(args.outdir)
    os.makedirs(out_dir, exist_ok=True)
    
    print(f"[*] Configuration parameters:")
    print(f"    - Domain: {args.domain}")
    print(f"    - Path:   {args.path}")
    print(f"    - Key:    {args.key}")
    print(f"    - Count:  {args.count}")
    
    # Generate Bridge Config
    bridge_cfg = generate_bridge_config(args.domain, args.path, args.key, args.count)
    bridge_file = os.path.join(out_dir, f"bridge_100_tunnels_{args.key}.json")
    print(f"[*] Writing Bridge config to: {bridge_file}")
    with open(bridge_file, "w", encoding="utf-8") as f:
        json.dump(bridge_cfg, f, indent=2)
        
    # Generate Portal Config
    portal_cfg = generate_portal_config(args.path, args.key, args.count)
    portal_file = os.path.join(out_dir, f"portal_100_tunnels_{args.key}.json")
    print(f"[*] Writing Portal config to: {portal_file}")
    with open(portal_file, "w", encoding="utf-8") as f:
        json.dump(portal_cfg, f, indent=2)
        
    print("[*] Successfully generated parallel tunnel configurations!")
