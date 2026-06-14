#!/usr/bin/env python3
"""
Dynamic Multicast Mesh Xray Unified Config Generator
Author: Antigravity

This script generates a highly optimized, dynamic, single-port CDN-style Xray JSON configuration
for the IDN mesh. It eliminates TCP port bloat by consolidating listeners:
1. IN_REVERSE_PORTAL (Port 10001) - Loopback VLESS inbound for all bridge reverse tunnels.
2. IN_XTLS_USER (Port 20001) - Loopback VLESS inbound for all user XTLS connections.

Traffic is dynamically routed in memory based on client emails:
- Users matching `{T}_{O}_{I}_{C}@user` route to `reverse-out-{T}_{O}_{I}_{C}`.
- Bridge channels route natively to DIRECT.
"""

import os
import json
import argparse

# ===================================================================
# CONFIG GENERATOR
# ===================================================================
def generate_unified_xray(tunnel_ids, outside_servers, inside_servers, cdns, uuid, seed):
    combinations = []
    for t in tunnel_ids:
        for o in outside_servers:
            for i in inside_servers:
                for c in cdns:
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
            "id": uuid,
            "email": f"{tag_suffix}@reverse",
            "seed": seed,
            "reverse": {
                "tag": outbound_tag
            }
        })

        # 2. Add User VLESS client object
        user_clients.append({
            "id": uuid,
            "email": f"{tag_suffix}@user",
            "seed": seed
        })

        # 3. User Routing Rule: Map user email to its dynamic reverse outbound tag
        routing_rules.append({
            "type": "field",
            "user": [f"{tag_suffix}@user"],
            "outboundTag": outbound_tag
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
    parser = argparse.ArgumentParser(description="Generate Xray Unified Config")
    parser.add_argument("--outside", type=str, default="01,03", help="Comma-separated list of active outside servers")
    parser.add_argument("--inside", type=str, default="01,03,04,05", help="Comma-separated list of active inside servers")
    parser.add_argument("--cdns", type=str, default="01,05", help="Comma-separated list of active CDNs")
    parser.add_argument("--tunnels", type=str, default=",".join([f"{i:02d}" for i in range(1, 25)]), help="Comma-separated list of tunnel IDs")
    parser.add_argument("--uuid", type=str, default="58764c09-99c3-4496-9591-9cff83e4c7b7", help="VLESS UUID")
    parser.add_argument("--seed", type=str, default="a3f5c8d2e9b1f4a7c6d8e2f1b5a9c3d7", help="VLESS Seed")
    parser.add_argument("--output", type=str, default="configs/xray/generated", help="Output directory")
    
    args = parser.parse_args()

    outside_servers = args.outside.split(",")
    inside_servers = args.inside.split(",")
    cdns = args.cdns.split(",")
    tunnel_ids = args.tunnels.split(",")

    out_dir = os.path.abspath(args.output)
    os.makedirs(out_dir, exist_ok=True)
    
    config, excludes = generate_unified_xray(
        tunnel_ids, outside_servers, inside_servers, cdns, 
        args.uuid, args.seed
    )
    
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
