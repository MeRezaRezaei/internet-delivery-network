# IDN Network Topology

## Overview
The Internet Delivery Network (IDN) uses a "Reverse-Reverse Proxy" pattern to bridge external servers (Bridges) to internal Iranian servers (Portals) via obfuscated tunnels.

## Nodes

### 1. External (Bridges)
- **Server 09 (Leo)**: Primary US Bridge. Initiates tunnels to Server 07.
  - Public IP: (Managed by Tailscale/Cloudflare)
  - Internal IP: `100.100.5.100` (Tailscale), `10.255.1.9` (Mesh)
- **Server 08 (DE)**: German Bridge.
- **Server 10 (DE)**: German Bridge.

### 2. Iranian Gateways (Portals)
- **Server 07 (Free)**: Primary Gateway/Portal.
  - Public IP: `185.204.197.242`
  - Internal IP: `10.255.1.7` (Mesh)
  - Domains: `i-07.doctel.ir` (via Cloudflare/ArvanCloud), `i-07.menudigi.ir` (via ArvanCloud)
- **Server 04 (Shiraz)**: Jump Host.
  - Public IP: `5.145.113.134`
  - Internal IP: `10.255.1.4` (Mesh)

## Traffic Flow (Reverse Proxy)
1. **Bridge (srv09)** initiates an XHTTP connection to `https://i-07.doctel.ir/23-01-07-05`.
2. **Cloudflare/ArvanCloud** proxies the traffic to **Server 07 Public IP (443)**.
3. **HAProxy (srv07)** receives the traffic:
   - Matches path `/23-01-07-05`.
   - Forwards to **Xray (srv07)** on port `23075`.
4. **Xray (srv07)** registers the reverse tunnel.
5. Traffic from **Internal Nodes (01, 03, etc.)** can now reach the Internet via the reverse tunnel.

## Credentials
- **SSH User**: `merezarezaei` (Sudo access)
- **Primary Password**: `asdfjkl` (Stored in `docs/project_brain/NETWORK_AND_ARCHITECTURE.md`)
- **Root Password**: `Rez@9011438678`
