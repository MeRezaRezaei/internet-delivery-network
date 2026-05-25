# Project Context

> [!CAUTION]
> # CRITICAL SECURITY MANDATE: SERVER 07 ABSOLUTE ZERO-TOUCH POLICY
> **Server 07 is the core, irreplaceable "Pro" management gateway. ANY configuration edit, systemctl reload, or ACTIVE testing/probing (including TLS handshakes, pings, traceroutes, or curls) targeting Server 07 will instantly expose it to GFW reputation blocking, risking permanent network lockout.**
> **DO NOT touch, modify, restart services, or execute any network/probing tests targeting these Server 07 identifiers from ANY server or environment:**
> *   **Public IP**: `185.204.197.242`
> *   **Private IP**: `10.255.1.7`
> *   **Domains**: Any domain starting with `i-07` (e.g. `i-07.menudigi.ir`, `i-07.doctel.ir`)
> **This mandate takes absolute precedence over all other tasks, scripts, and requests.**



## Overview
- Product name: Internet Delivery Network (IDN)
- Primary objective: Build a multicast Internet Delivery Network (similar to a CDN, but for internet access) to provide on-demand, uncensored global internet access in Iran, circumventing the restrictive "pro internet" (whitelist-based National Information Network).
- Tech stack: Xray-core (VLESS Reverse Proxy), Wireguard, Tailscale, Mikrotik RouterOS, Marzban (Docker), Technitium DNS (Docker), MySQL (Docker).
- Runtime model (Docker/cloud/local): Distributed Linux servers and routers.
- Deployment target: A hybrid network of domestic Iranian servers (restricted) and external servers (unrestricted), bridged primarily via Server 07.
- Critical business invariants: High availability, dynamic tunneling, stealth (evading DPI and whitelisting mechanisms), secure delivery. Server 07 cannot be used for selling access directly; it is a gateway.
- Out-of-scope boundaries: Server 02 (decommissioned). Servers 08 and 10 (not owned, restricted use).

## Documentation
- **Architecture and Topology**: See [NETWORK_AND_ARCHITECTURE.md](./NETWORK_AND_ARCHITECTURE.md) and [TOOLSET_ORCHESTRATION.md](./TOOLSET_ORCHESTRATION.md) for detailed IP mapping, server roles, and critical toolset relationships.
- **Operational Safety**: See [OPERATIONAL_SAFETY.md](./OPERATIONAL_SAFETY.md) for mandatory backup and rollback procedures.

## Notes
The project is currently transitioning from manual management to an automated, documented system via the AI Brain.
