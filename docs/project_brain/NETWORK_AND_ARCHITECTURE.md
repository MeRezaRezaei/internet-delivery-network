# Network and Architecture

## Infrastructure Topology

The network consists of a hybrid structure bridging restricted domestic servers in Iran with unrestricted external servers via a tiered architecture. 
### Core Nodes (Wireguard Subnet: 10.255.1.x)
- **01 shahriar** (95.38.180.145, 10.255.1.1) - Internal (Restricted)
- **03 bamdad** (188.121.119.237, 10.255.1.3) - Internal (Restricted)
- **04 shiraz** (5.145.113.134, 10.255.1.4) - Internal (Restricted)
- **05 mik** (62.220.123.35, 10.255.1.5) - Mikrotik Router (Manages Wireguard)
- **06 modem** (94.101.133.80, 10.255.1.6) - Internal (Restricted)
- **07 free** (185.204.197.242, 10.255.1.7) - **Gateway Node**.
  - **Docker Stack**: Marzban (Portal Management).
  - **Native Stack**: MySQL (Marzban DB), Technitium DNS (Primary Resolver).
  - **Ports**: 5011 (XTLS/XHTTP 21-08-07-05), 5012 (XTLS/XHTTP 24-01-07-06).

---

## 3. Toolset Integration (srv07)

### A. Technitium DNS
- **Role**: Stealth resolver and blocking engine.
- **Function**: Handles all recursive DNS queries for the IDN mesh. Prevents hijacking by forwarding queries over encrypted channels.
- **Management**: Web UI on port `5380`.

### B. Marzban Orchestration
- **Role**: Dynamic Portal Management.
- **Configuration**: `/opt/marzban/xray_config.json`.
- **Traffic Path**: 
  - External Bridge -> CDN -> srv07 HAProxy (443) -> srv07 Xray (5011/5012) -> Marzban managed clients.
  - Marzban Xray then routes traffic to SOCKS outbounds (21081/24081) which represent the final delivery points.

---

## Advanced Routing: The "Reverse-Reverse" Proxy (v26 Standard)

- **09 US Server** (Target testing environment)
- **10 DE Server** (Not owned)

### Access Credentials
- **SSH Key**: Located at `~/.ssh/id_rsa_tailscale`
- **Known Passwords**: `asdfjkl`, `Rez@9011438678`

---

## Advanced Routing: The "Reverse-Reverse" Proxy (v26 Standard)

To maintain the "Pro Internet" status of Server 07 and avoid direct commercial exposure, the project utilizes a "Reverse-Reverse" proxy pattern using Xray-core v26's **Simplified VLESS Reverse Proxy** over **XHTTP**.

### The "Trick" (v26 Alignment)
- **Server 07 (Iran, Gateway)** acts as the **Portal**. It listens for incoming connections (usually behind HAProxy).
- **Server 08/09/10 (External)** acts as the **Bridge**. It initiates a connection *out* to Server 07.

### v26 Requirements & Tips (Verified)
1.  **Email Matching**: Both Portal and Bridge MUST have the same `email` field in the user object for the reverse tunnel to "register" correctly.
2.  **Seed Matching**: Matching `seed` fields are required for stable session derivation in XHTTP/VLESS.
3.  **Transport (XHTTP)**: Use `XHTTP` with `mode: "packet-up"` on the Bridge side for maximum compatibility.
4.  **Path Pattern**: Use unique paths like `/21-08-07-05` for all production tunnels.
5.  **Reverse Tag Placement**: 
    - **Portal**: The `reverse` tag is placed INSIDE the `clients` (user) object.
    - **Bridge**: The `reverse` tag is placed INSIDE the `settings` object of the outbound (Simplified format is verified to work).

### Configuration Details (v26 Verified)

#### Portal (Server 07 - Iran)
```json
{
    "log": { "loglevel": "warning" },
    "inbounds": [{
        "port": 21075,
        "listen": "127.0.0.1",
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
            "xhttpSettings": { "path": "/21-08-07-05" }
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
    "log": { "loglevel": "warning" },
    "outbounds": [{
        "protocol": "vless",
        "tag": "tunnel",
        "settings": {
            "address": "185.204.197.242",
            "port": 443,
            "id": "UUID",
            "email": "de08@reverse",
            "encryption": "none",
            "seed": "SEED",
            "reverse": { "tag": "reverse-in-08" }
        },
        "streamSettings": {
            "network": "XHTTP",
            "security": "tls",
            "tlsSettings": { "serverName": "i-07.doctel.ir", "allowInsecure": true },
            "xhttpSettings": { "path": "/21-08-07-05", "mode": "packet-up" }
        }
    }],
    "routing": {
        "rules": [{ "type": "field", "inboundTag": ["reverse-in-08"], "outboundTag": "direct" }]
    }
}
```

---

## Tunnel Classifications & Maintenance Rules

The IDN operates two distinct types of network tunnels with strict operational boundaries:

### 1. Static Tunnels
* **Purpose:** Highly static connections used exclusively for administrative access, management services, and connecting staff to external servers/networks.
* **Safety Mandate:** These tunnels are **read-only/zero-touch** for general AI agents. DO NOT modify, test, or alter these configurations.
* **Access Control:** Server 07 (Portal) configuration falls strictly under this static class and must never be altered or tested by the agent.

> [!CAUTION]
> ### 🚨 The Three Unbreakable Direct Tunnels (Strict Zero-Touch Mandate)
> These represent the core management and communication lifelines of the IDN. They **MUST NEVER be modified, stopped, restarted, or tested under any conditions**.
>
> 1. **US Direct Tunnel (`xray@mmd-pg-us.service`)**
>    - **Role:** Direct US to Server 07 VLESS XHTTP reverse tunnel.
>    - **Portal Config:** `/usr/local/etc/xray/mmd-pg-us.json` (Ports: `6443` [reverse], `7443` [VLESS])
>    - **Bridge Config (srv09):** `/usr/local/etc/xray/mmd-pg-us.json` (Connects to `185.204.197.242:6443`)
>    - **Static Backups:** [us_direct_portal.json](./static_unbreakable_tunnels/us_direct_portal.json) | [us_direct_bridge.json](./static_unbreakable_tunnels/us_direct_bridge.json)
>
> 2. **DE Direct Tunnel (`xray@mmd-pg.service`)**
>    - **Role:** Direct Germany to Server 07 VLESS XHTTP reverse tunnel.
>    - **Portal Config:** `/usr/local/etc/xray/mmd-pg.json` (Ports: `8443` [reverse], `9443` [VLESS])
>    - **Bridge Config (de-server):** `/usr/local/etc/xray/mmd-pg.json` (Connects to `185.204.197.242:8443`)
>    - **Static Backups:** [de_direct_portal.json](./static_unbreakable_tunnels/de_direct_portal.json) | [de_direct_bridge.json](./static_unbreakable_tunnels/de_direct_bridge.json)
>
> 3. **DE-PG Direct Tunnel (`xray@mmd-pg-de.service`)**
>    - **Role:** Direct Germany-PG to Server 07 VLESS XHTTP reverse tunnel.
>    - **Portal Config:** `/usr/local/etc/xray/mmd-pg-de.json` (Ports: `4443` [reverse], `5443` [VLESS])
>    - **Bridge Config (Pubg-Sell):** `/usr/local/etc/xray/mmd-pg-de.json` (Connects to `185.204.197.242:4443`)
>    - **Static Backups:** [de_pg_direct_portal.json](./static_unbreakable_tunnels/de_pg_direct_portal.json) | [de_pg_direct_bridge.json](./static_unbreakable_tunnels/de_pg_direct_bridge.json)

### 2. Dynamic Tunnels
* **Purpose:** Multi-user client access routed dynamically through Marzban nodes.
* **Safety Mandate:** These configurations are safe to touch, refine, and optimize. They are managed directly by Marzban nodes or generated by the Marzban Xray node itself.

---

## Server Roles and Access Summary

| ID | Name | Role | IP (Internal) | IP (Public) | Access Path | SSH Port |
|---|---|---|---|---|---|---|
| 04 | shiraz | Jump Host | 10.255.1.4 | 5.145.113.134 | Direct SSH (Pass: asdfjkl) | 22 |
| 07 | free | Portal | 10.255.1.7 | 185.204.197.242 | SSH via 04 (Key: id_rsa_idn) | 22 |
| 08 | DE | Bridge | 10.255.1.8 | - | SSH via 07 (Key/Agent Forwarding) | 2022 (Mesh) |
| 09 | leo | Bridge (US) | 10.255.1.9 | - | SSH via 07 (Key/Agent Forwarding) | 2022 (Mesh) |
| 10 | DE | Bridge | 10.255.1.10 | - | SSH via 07 (Key/Agent Forwarding) | 2022 (Mesh) |

> [!NOTE]
> SSH to the external bridges (srv08, srv09, srv10) over the private Wireguard/Mesh network (`10.255.1.x`) and Tailscale (`100.100.5.x`) is explicitly bound to **Port 2022**. Connecting on the default port 22 will result in `Connection refused`. Only `srv04` and `srv07` listen on Port 22.

