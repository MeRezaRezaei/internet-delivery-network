#!/usr/bin/env python3
"""
Multicast Mesh HAProxy Config Generator
Author: Antigravity

This script automatically generates customized, high-performance HAProxy configurations
for every inside server in the IDN mesh. It implements the "Multicast Routing" pattern
where paths serve as keys:
    /{tunnel_id}-{outside_server_id}-{inside_server_id}-{cdn_id}
    or
    /{tunnel_id}/{outside_server_id}/{inside_server_id}/{cdn_id}

If the {inside_server_id} in the path matches the current node itself, HAProxy routes
the traffic locally to Xray (127.0.0.1). Otherwise, it automatically routes the traffic
across the Wireguard mesh to the target inside server's private IP (10.255.1.x), making
the entire routing fabric independent and dynamic!
"""

import os
import argparse

# ===================================================================
# INVENTORY CONFIGURATION
# ===================================================================
TUNNEL_IDS = [f"{i:02d}" for i in range(1, 25)]
OUTSIDE_SERVERS = ["01", "02", "03"]
CDNS = ["01", "02", "03", "04", "05", "06"]

INSIDE_SERVERS = {
    "01": "10.255.1.1",  # shahriar
    "02": "10.255.1.2",  # server 2
    "03": "10.255.1.3",  # bamdad
    "04": "10.255.1.4",  # shiraz
    "05": "10.255.1.5",  # mik (Mikrotik Router)
    "06": "10.255.1.6"   # modem
}

# Subdomain settings matching the premium HAProxy profile
SUBDOMAINS = {
    "tech.new-state.ir": "127.0.0.1:5380",      # Technitium DNS Management
    "dash.new-state.ir": "127.0.0.1:8002",      # Migrated PUBG Panel (400 Users)
    "panel.new-state.ir": "127.0.0.1:2020",     # Legacy Panel (5 Users)
    "headscale.menudigi.ir": "127.0.0.1:8081",  # Headscale VPN
    "pma.menudigi.ir": "127.0.0.1:8080",        # phpMyAdmin
    "dash.menudigi.ir": "127.0.0.1:2020",       # Alternate Marzban Panel
    "sub.menudigi.ir": "127.0.0.1:2020",        # Subscription Endpoint
    "tech.menudigi.ir": "127.0.0.1:5380"        # Technitium DNS
}

# ===================================================================
# PORT DERIVATION FORMULAS
# ===================================================================
def get_derived_reverse_port(tunnel_id, outside_id, inside_id, cdn_id):
    """
    Type 1: Bridge-to-Portal (Reverse Tunnel) Listening Port
    Formula: 10000 + (T * 1000) + (O * 100) + (I * 10) + C
    E.g. Tunnel 05, Outside 01, Inside 03, CDN 01 -> Port 15131
    """
    return 10000 + (int(tunnel_id) * 1000) + (int(outside_id) * 100) + (int(inside_id) * 10) + int(cdn_id)

def get_derived_xtls_port(tunnel_id, outside_id, inside_id, cdn_id):
    """
    Type 2: User XTLS Port (User-to-HAProxy/Xray)
    Formula: 20000 + (T * 1000) + (O * 100) + (I * 10) + C
    E.g. Tunnel 05, Outside 01, Inside 03, CDN 01 -> Port 25131
    """
    return 20000 + (int(tunnel_id) * 1000) + (int(outside_id) * 100) + (int(inside_id) * 10) + int(cdn_id)

def get_derived_socks_port(tunnel_id, outside_id, inside_id, cdn_id):
    """
    Type 3: SOCKS Delivery Port
    Formula: 30000 + (T * 1000) + (O * 100) + (I * 10) + C
    E.g. Tunnel 05, Outside 01, Inside 03, CDN 01 -> Port 35131
    """
    return 30000 + (int(tunnel_id) * 1000) + (int(outside_id) * 100) + (int(inside_id) * 10) + int(cdn_id)

# ===================================================================
# CONFIG TEMPLATE GENERATOR
# ===================================================================
def generate_haproxy_cfg(node_id):
    if node_id not in INSIDE_SERVERS:
        raise ValueError(f"Node ID {node_id} is not in inside servers inventory!")

    node_ip = INSIDE_SERVERS[node_id]
    
    cfg = []
    
    # 1. Global Section (Premium performance tunings)
    cfg.append("""global
    log stdout format raw local0
    maxconn 20000
    stats socket /var/run/haproxy.sock mode 660 level admin expose-fd listeners
    stats timeout 30s
    daemon

    # Buffer and TCP Performance Tuning for XHTTP Streams
    tune.bufsize 65536
    tune.maxrewrite 8192
    tune.h2.initial-window-size 2147483647
    tune.quic.fe.max-idle-timeout 60000

    # Modern Security Ciphers
    ssl-default-bind-ciphersuites TLS_AES_128_GCM_SHA256:TLS_AES_256_GCM_SHA384:TLS_CHACHA20_POLY1305_SHA256
    ssl-default-bind-options ssl-min-ver TLSv1.2 no-tls-tickets
""")

    # 2. Defaults Section
    cfg.append("""defaults
    mode http
    log global
    option httplog
    option dontlognull
    option forwardfor
    
    # Smart Multiplexing & Splicing for High-Throughput Tunnels
    option tcp-smart-connect
    option tcp-smart-accept
    no option http-buffer-request
    
    timeout connect 5s
    timeout client 600s
    timeout server 600s
    timeout tunnel 1h
""")

    # 3. HTTP Frontend (Redirects or transparent routing)
    cfg.append(f"""# ---------------------------------------------------------------------
# FRONTEND: HTTP (Port 80)
# ---------------------------------------------------------------------
frontend main_http
    bind :::80 v4v6
    mode http

    # Dynamic Redirect to HTTPS except for reverse tunnels that bypass SSL
    http-request redirect scheme https if !{{ ssl_fc }}
""")

    # 4. HTTPS Frontend (Unified entrypoint)
    cfg.append(f"""# ---------------------------------------------------------------------
# FRONTEND: HTTPS & QUIC / HTTP/3 (Port 443)
# ---------------------------------------------------------------------
frontend main_https
    bind :::443 v4v6 ssl crt /etc/ssl/private/selfsigned.pem crt /etc/ssl/private/ehraz.pem alpn h2,http/1.1
    mode http
    
    # HTTP/3 QUIC Bindings for modern low-latency clients
    bind quic4@:443 ssl crt /etc/ssl/private/selfsigned.pem crt /etc/ssl/private/ehraz.pem alpn h3
    bind quic6@:443 ssl crt /etc/ssl/private/selfsigned.pem crt /etc/ssl/private/ehraz.pem alpn h3
    http-response set-header Alt-Svc "h3=\\":443\\"; ma=31536000"

    option forwardfor
    http-request set-header X-Forwarded-Proto https
    http-request set-header X-Forwarded-Host %[hdr(host)]
    http-request set-header X-Forwarded-Port 443

    # --- Static Unbreakable Admin Tunnels ---
    use_backend bk_mmd_pg_us if {{ path_beg /mmd-pg-us }}
    use_backend bk_mmd_pg if {{ path_beg /mmd-pg }}
    use_backend bk_mmd_pg_de if {{ path_beg /mmd-pg-de }}

    # --- Subdomain Panel / Service Routing ---
""")

    # Add Subdomain ACLs and routing
    for domain, target in SUBDOMAINS.items():
        domain_safe = domain.replace(".", "_").replace("-", "_")
        cfg.append(f"    acl is_{domain_safe} hdr(host) -i {domain}\n    use_backend bk_{domain_safe} if is_{domain_safe}")

    cfg.append("\n    # --- Dynamic Combinatorial Path Routing (Radix Tree Match) ---")

    # Generate ACLs and backend routing maps for all combinations
    # Supports both '-' and '/' delimiters in paths!
    for tunnel in TUNNEL_IDS:
        for out in OUTSIDE_SERVERS:
            for ins in INSIDE_SERVERS.keys():
                for cdn in CDNS:
                    path_key_dash = f"{tunnel}-{out}-{ins}-{cdn}"
                    path_key_slash = f"{tunnel}/{out}/{ins}/{cdn}"
                    backend_name = f"bk_{tunnel}_{out}_{ins}_{cdn}"
                    
                    # 1. Reverse Tunnels / VLESS Tunnels
                    cfg.append(f"    use_backend {backend_name}_vless if {{ path_beg /{path_key_dash} }} || {{ path_beg /{path_key_slash} }}")
                    
                    # 2. XTLS / User Tunnels
                    cfg.append(f"    use_backend {backend_name}_xtls if {{ path_beg /{path_key_dash}/xtls }} || {{ path_beg /{path_key_slash}/xtls }}")

    cfg.append("""
    # Fallback to local default resolver / doh backend
    default_backend bk_fallback
""")

    # 5. Static & Subdomain Backends Section
    cfg.append("""# ===================================================================
# BACKENDS: STATICS & SUBDOMAINS
# ===================================================================
backend bk_fallback
    mode http
    http-request deny deny_status 404

backend bk_mmd_pg_us
    mode http
    server srv_mmd_pg_us 127.0.0.1:9443 proto h2

backend bk_mmd_pg
    mode http
    server srv_mmd_pg 127.0.0.1:8443

backend bk_mmd_pg_de
    mode http
    server srv_mmd_pg_de 127.0.0.1:4443
""")

    for domain, target in SUBDOMAINS.items():
        domain_safe = domain.replace(".", "_").replace("-", "_")
        cfg.append(f"""backend bk_{domain_safe}
    mode http
    server srv_{domain_safe} {target}
""")

    cfg.append("\n# ===================================================================")
    cfg.append(f"# BACKENDS: COMBINATORIAL MULTICAST MESH (Generated for Node {node_id})")
    cfg.append("# ===================================================================")

    # Generate backends for all combinations
    for tunnel in TUNNEL_IDS:
        for out in OUTSIDE_SERVERS:
            for ins in INSIDE_SERVERS.keys():
                for cdn in CDNS:
                    backend_tag = f"{tunnel}_{out}_{ins}_{cdn}"
                    
                    # Determine target routing (Local Loopback vs Remote Wireguard Jump)
                    is_local = (ins == node_id)
                    target_ip = "127.0.0.1" if is_local else INSIDE_SERVERS[ins]
                    
                    # Derive 3-port scenario mapping
                    reverse_port = get_derived_reverse_port(tunnel, out, ins, cdn)
                    xtls_port = get_derived_xtls_port(tunnel, out, ins, cdn)
                    socks_port = get_derived_socks_port(tunnel, out, ins, cdn)

                    # 1. Reverse / VLESS Backend
                    cfg.append(f"backend bk_{backend_tag}_vless")
                    cfg.append("    mode http")
                    cfg.append("    no option http-buffer-request")
                    cfg.append("    timeout tunnel 1h")
                    cfg.append("    option splice-auto")
                    cfg.append("    option http-keep-alive")
                    cfg.append("    option forwardfor")
                    cfg.append(f"    # Scenario Ports: Reverse={reverse_port}, XTLS={xtls_port}, SOCKS={socks_port}")
                    
                    if is_local:
                        # Local Xray is a VLESS backend on loopback, binding to derived reverse tunnel port
                        cfg.append(f"    server local_xray_{reverse_port} {target_ip}:{reverse_port} check maxconn 5000\n")
                    else:
                        # Remote mesh target server over Wireguard directly to the peer's derived reverse tunnel port (no SSL)
                        cfg.append(f"    server remote_mesh_{ins}_{reverse_port} {target_ip}:{reverse_port} check maxconn 5000\n")

                    # 2. XTLS / User Connection Backend
                    cfg.append(f"backend bk_{backend_tag}_xtls")
                    cfg.append("    mode http")
                    cfg.append("    no option http-buffer-request")
                    cfg.append("    timeout tunnel 1h")
                    cfg.append("    option splice-auto")
                    cfg.append("    option http-keep-alive")
                    cfg.append("    option forwardfor")
                    cfg.append(f"    # Scenario Ports: Reverse={reverse_port}, XTLS={xtls_port}, SOCKS={socks_port}")
                    
                    if is_local:
                        # Local XTLS backend on loopback, binding to derived XTLS user port
                        cfg.append(f"    server local_xtls_{xtls_port} {target_ip}:{xtls_port} check maxconn 5000\n")
                    else:
                        # Remote mesh target server over Wireguard directly to the peer's derived XTLS user port (no SSL)
                        cfg.append(f"    server remote_mesh_{ins}_{xtls_port} {target_ip}:{xtls_port} check maxconn 5000\n")

    return "\n".join(cfg)

# ===================================================================
# MAIN INVOCATION ENTRYPOINT
# ===================================================================
if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Multicast HAProxy Config Compiler")
    parser.add_argument("--node", type=str, default="all", help="Target inside node ID (01, 03, 04, 05, 06, 07 or 'all')")
    parser.add_argument("--outdir", type=str, default="configs/haproxy/generated", help="Output directory for generated files")
    
    args = parser.parse_args()
    
    out_dir = os.path.abspath(args.outdir)
    os.makedirs(out_dir, exist_ok=True)
    
    targets = INSIDE_SERVERS.keys() if args.node == "all" else [args.node]
    
    print(f"[*] Starting compilation. Output directory: {out_dir}")
    print(f"[*] Compiling paths matrix: {len(TUNNEL_IDS)} tunnels x {len(OUTSIDE_SERVERS)} bridges x {len(INSIDE_SERVERS)} nodes x {len(CDNS)} CDNs = {len(TUNNEL_IDS)*len(OUTSIDE_SERVERS)*len(INSIDE_SERVERS)*len(CDNS)} combinations.")
    
    for t_node in targets:
        print(f"[+] Compiling HAProxy configuration for Node {t_node} (IP: {INSIDE_SERVERS[t_node]})...")
        config_content = generate_haproxy_cfg(t_node)
        file_path = os.path.join(out_dir, f"haproxy_node_{t_node}.cfg")
        with open(file_path, "w", encoding="utf-8") as f:
            f.write(config_content)
        print(f"    -> Compiled successfully! Saved to: {file_path}")
            
    print("[*] Compilation complete.")
