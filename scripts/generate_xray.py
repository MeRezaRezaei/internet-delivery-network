#!/usr/bin/env python3
"""
Multicast Mesh Xray Unified Config Generator
Author: Antigravity

This script generates a unified, replicated Xray JSON configuration for the IDN mesh.
It implements the direct reverse proxy routing pattern, eliminating SOCKS5 bridging.

For each scenario (/{tunnel_id}-{outside_server_id}-{inside_server_id}-{cdn_id}):
1. XTLS User Inbound (IN_{T}_{O}_{I}_{C}_XTLS) on Type 2 port (20000 + T*1000 + O*100 + I*10 + C)
2. Reverse Portal Inbound (IN_{T}_{O}_{I}_{C}_REVERSE_PORTAL) on Type 1 port (10000 + T*1000 + O*100 + I*10 + C)
3. Routing rule to directly pass user traffic from XTLS Inbound to the reverse proxy outbound tag.
4. Routing rule to route reverse-out proxy channel traffic to DIRECT.
"""

import os
import json

# ===================================================================
# INVENTORY CONFIGURATION
# ===================================================================
# Tunnel IDs 01 to 24 sequentially matching MySQL database auto-increment keys
TUNNEL_IDS = [f"{i:02d}" for i in range(1, 25)]
# 3 Outside servers
OUTSIDE_SERVERS = ["01", "02", "03"]
# 6 Inside servers
INSIDE_SERVERS = ["01", "02", "03", "04", "05", "06"]
# 6 CDNs
CDNS = ["01", "02", "03", "04", "05", "06"]

# Common Credentials
UUID = "58764c09-99c3-4496-9591-9cff83e4c7b7"
SEED = "a3f5c8d2e9b1f4a7c6d8e2f1b5a9c3d7"

# ===================================================================
# PORT DERIVATION FORMULAS
# ===================================================================
def get_derived_reverse_port(t, o, i, c):
    return 10000 + (int(t) * 1000) + (int(o) * 100) + (int(i) * 10) + int(c)

def get_derived_xtls_port(t, o, i, c):
    return 20000 + (int(t) * 1000) + (int(o) * 100) + (int(i) * 10) + int(c)

# ===================================================================
# CONFIG GENERATOR
# ===================================================================
def generate_unified_xray():
    inbounds = []
    routing_rules = []
    exclude_tags = []

    print("[*] Generating 2592 scenario combinations...")

    # Pre-populate list of combinations
    combinations = []
    for t in TUNNEL_IDS:
        for o in OUTSIDE_SERVERS:
            for i in INSIDE_SERVERS:
                for c in CDNS:
                    combinations.append((t, o, i, c))

    for idx, (t, o, i, c) in enumerate(combinations):
        tag_suffix = f"{t}_{o}_{i}_{c}"
        
        xtls_tag = f"IN_{tag_suffix}_XTLS"
        reverse_tag = f"IN_{tag_suffix}_REVERSE_PORTAL"
        outbound_tag = f"reverse-out-{t}_{o}_{i}_{c}"
        
        reverse_port = get_derived_reverse_port(t, o, i, c)
        xtls_port = get_derived_xtls_port(t, o, i, c)
        
        # 1. XTLS Inbound for general users
        xtls_inbound = {
            "tag": xtls_tag,
            "port": xtls_port,
            "listen": "0.0.0.0",
            "protocol": "vless",
            "settings": {
                "clients": [],
                "decryption": "none"
            },
            "streamSettings": {
                "network": "xhttp",
                "xhttpSettings": {
                    "path": f"/{t}-{o}-{i}-{c}/xtls",
                    "mode": "auto"
                }
            }
        }
        inbounds.append(xtls_inbound)
        
        # 2. Reverse Portal Inbound for the Bridge registration
        reverse_inbound = {
            "tag": reverse_tag,
            "port": reverse_port,
            "listen": "127.0.0.1",
            "protocol": "vless",
            "settings": {
                "clients": [
                    {
                        "id": UUID,
                        "email": f"{t}_{o}_{i}_{c}@reverse",
                        "seed": SEED,
                        "reverse": {
                            "tag": outbound_tag
                        }
                    }
                ],
                "decryption": "none"
            },
            "streamSettings": {
                "network": "xhttp",
                "xhttpSettings": {
                    "path": f"/{t}-{o}-{i}-{c}",
                    "mode": "auto"
                }
            }
        }
        inbounds.append(reverse_inbound)
        
        # Keep track of excluded tags for Marzban configuration
        exclude_tags.append(reverse_tag)
        
        # 3. Routing Rules: Route XTLS Inbound directly to the registered reverse proxy outbound
        routing_rules.append({
            "type": "field",
            "inboundTag": [xtls_tag],
            "outboundTag": outbound_tag
        })
        
        # 4. Routing Rules: Route incoming traffic from the reverse proxy channel to DIRECT
        routing_rules.append({
            "type": "field",
            "inboundTag": [outbound_tag],
            "outboundTag": "DIRECT"
        })

    # Add standard final block rule
    routing_rules.append({
        "type": "field",
        "port": "0-65535",
        "outboundTag": "BLOCK"
    })

    # Assemble Xray unified config JSON structure
    xray_config = {
        "log": {
            "loglevel": "debug"
        },
        "inbounds": inbounds,
        "outbounds": [
            {
                "protocol": "freedom",
                "tag": "DIRECT"
            },
            {
                "protocol": "blackhole",
                "tag": "BLOCK"
            }
        ],
        "routing": {
            "domainStrategy": "AsIs",
            "rules": routing_rules
        }
    }

    return xray_config, exclude_tags

# ===================================================================
# MAIN INVOCATION ENTRYPOINT
# ===================================================================
if __name__ == "__main__":
    out_dir = os.path.abspath("configs/xray/generated")
    os.makedirs(out_dir, exist_ok=True)
    
    config, excludes = generate_unified_xray()
    
    # 1. Save unified Xray config
    config_file = os.path.join(out_dir, "xray_unified.json")
    print(f"[*] Writing unified Xray configuration to: {config_file}")
    with open(config_file, "w", encoding="utf-8") as f:
        json.dump(config, f, indent=2)
    print("    -> Config file written successfully!")
    
    # 2. Save exclude tags list (one per line)
    exclude_file = os.path.join(out_dir, "exclude_tags.txt")
    print(f"[*] Writing Marzban exclude inbound tags list to: {exclude_file}")
    with open(exclude_file, "w", encoding="utf-8") as f:
        f.write("\n".join(excludes) + "\n")
    print("    -> Exclude tags file written successfully!")
    
    # 3. Save exclude tags as a single comma-separated list
    exclude_csv_file = os.path.join(out_dir, "exclude_tags_csv.txt")
    print(f"[*] Writing comma-separated Marzban exclude inbound tags to: {exclude_csv_file}")
    with open(exclude_csv_file, "w", encoding="utf-8") as f:
        f.write(",".join(excludes) + "\n")
    print("    -> Comma-separated exclude tags file written successfully!")
    
    print("[*] All processes finished successfully.")
