# Network and Architecture

## Infrastructure Topology

The network consists of a hybrid structure bridging restricted domestic servers in Iran with unrestricted external servers via a tiered architecture. 

### Core Nodes (Wireguard Subnet: 10.255.1.x)
- **01 shahriar** (95.38.180.145, 10.255.1.1) - Internal (Restricted)
- **02 shahin** (OFFLINE/DELETED)
- **03 bamdad** (188.121.119.237, 10.255.1.3) - Internal (Restricted)
- **04 shiraz** (5.145.113.134, 10.255.1.4) - Internal (Restricted)
- **05 mik** (62.220.123.35, 10.255.1.5) - Mikrotik Router (Manages Wireguard)
- **06 modem** (94.101.133.80, 10.255.1.6) - Internal (Restricted)
- **07 free** (185.204.197.242, 10.255.1.7) - **Gateway Node**. Has "Pro Internet" access (global internet privileges). Connected via Tailscale to nodes 8, 9, and 10.

### External Nodes (via Tailscale on Server 07)
- **08 DE Server** (Not owned)
- **09 US Server** (Target testing environment)
- **10 DE Server** (Not owned)

### Access Credentials
- **SSH Key**: Located at `~/.ssh/id_rsa_tailscale`
- **Known Passwords**: `asdfjkl`, `Rez@9011438678`

---

## Advanced Routing: The "Reverse-Reverse" Proxy (v26 Standard)

To maintain the "Pro Internet" status of Server 07 and avoid direct commercial exposure, the project utilizes a "Reverse-Reverse" proxy pattern using Xray-core v26's **Simplified VLESS Reverse Proxy** over **XHTTP**.

### The "Trick" (v26 Alignment)
- **Server 07 (Iran, Gateway)** acts as the **Portal**. It listens for incoming connections.
- **Server 08/09/10 (External)** acts as the **Bridge**. It initiates a connection *out* to Server 07.

### v26 Requirements & Tips
1.  **Email Matching**: Both Portal and Bridge MUST have the same `email` field in the user object for the reverse tunnel to "register" correctly.
2.  **Seed Matching**: Matching `seed` fields are required for stable session derivation in XHTTP/VLESS.
3.  **Transport (XHTTP)**: Use `XHTTP` with `mode: "packet-up"` for maximum compatibility when fronting with CDNs (ArvanCloud/Cloudflare).
4.  **Path Pattern**: Use unique paths like `/c-[target]-[portal]-[intermediate]` (e.g., `/c-08-07-05`) for all production tunnels.
5.  **Reverse Tag Placement**: 
    - **Portal**: The `reverse` tag is placed INSIDE the `clients` (user) object.
    - **Bridge**: The `reverse` tag is placed INSIDE the `settings` object of the outbound (not inside the user).

### Configuration Details (v26 Syntax)

#### Portal (Server 07 - Iran)
```json
{
    "inbounds": [{
        "port": 21075,
        "protocol": "vless",
        "settings": {
            "clients": [{
                "id": "UUID",
                "email": "de08@reverse",
                "seed": "SEED",
                "reverse": { "tag": "reverse-out-08" }
            }]
        },
        "streamSettings": {
            "network": "XHTTP",
            "xhttpSettings": { "path": "/path", "mode": "auto" }
        }
    }],
    "routing": {
        "rules": [
            { "type": "field", "inboundTag": ["socks-in"], "outboundTag": "reverse-out-08" },
            { "type": "field", "inboundTag": ["reverse-out-08"], "outboundTag": "direct" }
        ]
    }
}
```

#### Bridge (Server 08 - Germany)
```json
{
    "outbounds": [{
        "protocol": "vless",
        "tag": "tunnel",
        "settings": {
            "vnext": [{
                "address": "i-07.doctel.ir",
                "port": 443,
                "users": [{
                    "id": "UUID",
                    "email": "de08@reverse",
                    "seed": "SEED"
                }]
            }],
            "reverse": {
                "tag": "reverse-in-08",
                "sniffing": { "enabled": true, "destOverride": ["http", "tls"] }
            }
        },
        "streamSettings": {
            "network": "XHTTP",
            "security": "tls",
            "tlsSettings": { "serverName": "i-07.doctel.ir", "allowInsecure": true },
            "xhttpSettings": { "path": "/path", "mode": "packet-up" }
        }
    }],
    "routing": {
        "rules": [{ "type": "field", "inboundTag": ["reverse-in-08"], "outboundTag": "direct" }]
    }
}
```

---

## Server Roles and Access Summary

| ID | Name | Role | IP (Internal) | IP (Public) | Access Path |
|---|---|---|---|---|---|
| 04 | shiraz | Jump Host | 10.255.1.4 | 5.145.113.134 | Direct SSH (Pass: asdfjkl) |
| 07 | free | Portal | 10.255.1.7 | 185.204.197.242 | SSH via 04 (Key: id_rsa_tailscale) |
| 09 | leo | Bridge | 100.100.5.100 | - | SSH via 07 (Key/Agent Forwarding) |
