#!/usr/bin/env python3
"""
Dynamic Multicast Mesh Xray Unified Config Generator
Author: Antigravity

This script generates a highly optimized, dynamic, single-port CDN-style Xray JSON configuration
for the IDN mesh. It eliminates TCP port bloat by consolidating listeners:
1. IN_REVERSE_PORTAL (Port 10001) - Loopback VLESS inbound for all 384 bridge reverse tunnels.
2. IN_XTLS_USER (Port 20001) - Loopback VLESS inbound for all 384 user XTLS connections.

Traffic is dynamically routed in memory based on client emails:
- Users matching `{T}_{O}_{I}_{C}@user` route to `reverse-out-{T}_{O}_{I}_{C}`.
- Bridge channels route natively to DIRECT.
"""

import os
import json

# ===================================================================
# INVENTORY CONFIGURATION (Active Matrix matching generate_haproxy)
# ===================================================================
TUNNEL_IDS = [f"{i:02d}" for i in range(1, 25)]
OUTSIDE_SERVERS = ["01", "03"]
INSIDE_SERVERS = ["01", "03", "04", "05"]
CDNS = ["01", "05"]

# Common Credentials
UUID = "58764c09-99c3-4496-9591-9cff83e4c7b7"
SEED = "a3f5c8d2e9b1f4a7c6d8e2f1b5a9c3d7"

# ===================================================================
# CONFIG GENERATOR
# ===================================================================
def generate_unified_xray():
    combinations = []
    for t in TUNNEL_IDS:
        for o in OUTSIDE_SERVERS:
            for i in INSIDE_SERVERS:
                for c in CDNS:
                    combinations.append((t, o, i, c))

    print(f"[*] Generating dynamic configuration for {len(combinations)} active scenario combinations...")

    reverse_clients = []
    user_clients = []
    routing_rules = []
    reverse_out_tags = []

    for t, o, i, c in combinations:
        tag_suffix = f"{t}_{o}_{i}_{c}"
        outbound_tag = f"reverse-out-{t}_{o}_{i}_{c}"
        reverse_out_tags.append(outbound_tag)

        # 1. Add Bridge Reverse Portal client object
        reverse_clients.append({
            "id": UUID,
            "email": f"{tag_suffix}@reverse",
            "seed": SEED,
            "reverse": {
                "tag": outbound_tag
            }
        })

        # 2. Add User VLESS client object
        user_clients.append({
            "id": UUID,
            "email": f"{tag_suffix}@user",
            "seed": SEED
        })

        # 3. User Routing Rule: Map user email to its dynamic reverse outbound tag
        routing_rules.append({
            "type": "field",
            "user": [f"{tag_suffix}@user"],
            "outboundTag": outbound_tag
        })

    # 4. Consolidated Reverse Channel routing rule to DIRECT
    routing_rules.append({
        "type": "field",
        "inboundTag": reverse_out_tags,
        "outboundTag": "DIRECT"
    })

    # 5. Fallback final block rule
    routing_rules.append({
        "type": "field",
        "port": "0-65535",
        "outboundTag": "BLOCK"
    })

    # Assemble dynamic inbounds
    inbounds = [
        {
            "tag": "IN_REVERSE_PORTAL",
            "port": 10001,
            "listen": "127.0.0.1",
            "protocol": "vless",
            "settings": {
                "clients": reverse_clients,
                "decryption": "none"
            },
            "streamSettings": {
                "network": "xhttp",
                "xhttpSettings": {
                    "path": "/reverse",
                    "mode": "auto"
                }
            }
        },
        {
            "tag": "IN_XTLS_USER",
            "port": 20001,
            "listen": "127.0.0.1",
            "protocol": "vless",
            "settings": {
                "clients": user_clients,
                "decryption": "none"
            },
            "streamSettings": {
                "network": "xhttp",
                "xhttpSettings": {
                    "path": "/xtls",
                    "mode": "auto"
                }
            }
        }
    ]

    xray_config = {
        "log": {
            "loglevel": "warning"
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

    # In single-port mode, only the single loopback reverse portal needs to be excluded
    exclude_tags = ["IN_REVERSE_PORTAL"]

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
    print(f"[*] Writing dynamic Xray configuration to: {config_file}")
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
