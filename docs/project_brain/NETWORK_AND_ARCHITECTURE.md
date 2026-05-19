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

## Advanced Routing: The "Reverse-Reverse" Proxy

To maintain the "Pro Internet" status of Server 07 and avoid direct commercial exposure, the project utilizes a "Reverse-Reverse" proxy pattern using Xray-core's Simplified VLESS Reverse Proxy.

### The "Trick"
Contrary to typical setups where the external server is the Portal, this architecture flips the roles:
- **Server 07 (Iran, Gateway)** acts as the **Portal**. It listens for incoming connections from the Bridge.
- **Server 09 (US, leo)** acts as the **Bridge**. It initiates a connection *out* to Server 07.

### Why this works
1. **Traffic Direction**: The Bridge (US) connects to the Portal (Iran). This established tunnel is then used to route traffic from Iran to the US.
2. **Masking**: Traffic exiting to the global internet appears to originate from Server 09 (US), but it is carried over the "Pro" link of Server 07.
3. **Safety**: Server 07 is not directly "selling" or "exiting" traffic, which protects its "Pro" status.

### Configuration Details (Simplified Syntax)

#### Portal (Server 07 - Iran)
```json
{
    "inbounds": [
        {
            "port": 6443,
            "protocol": "vless",
            "settings": {
                "clients": [{
                    "id": "...",
                    "reverse": { "tag": "portal" } // Virtual tag for the tunnel
                }]
            }
        }
    ],
    "routing": {
        "rules": [
            {
                "type": "field",
                "inboundTag": ["internal-user-inbound"], 
                "outboundTag": "portal" // Pushes data DOWN to the US Bridge
            }
        ]
    }
}
```

#### Bridge (Server 09 - US)
```json
{
    "outbounds": [
        {
            "protocol": "vless",
            "settings": {
                "address": "185.204.197.242", // Server 07 IP
                "port": 6443,
                "reverse": { "tag": "bridge" } // Virtual tag for traffic FROM the tunnel
            }
        }
    ],
    "routing": {
        "rules": [
            {
                "type": "field",
                "inboundTag": ["bridge"], // Traffic arriving FROM Iran
                "outboundTag": "direct"   // Exits to Global Internet
            }
        ]
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
