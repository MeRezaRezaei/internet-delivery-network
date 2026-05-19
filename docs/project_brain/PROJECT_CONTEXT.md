# Project Context

## Overview
- Product name: Internet Delivery Network (IDN)
- Primary objective: Build a multicast Internet Delivery Network (similar to a CDN, but for internet access) to provide on-demand, uncensored global internet access in Iran, circumventing the restrictive "pro internet" (whitelist-based National Information Network).
- Tech stack: Xray-core (VLESS Reverse Proxy), Wireguard, Tailscale, Mikrotik RouterOS.
- Runtime model (Docker/cloud/local): Distributed Linux servers and routers.
- Deployment target: A hybrid network of domestic Iranian servers (restricted) and external servers (unrestricted), bridged primarily via Server 07.
- Critical business invariants: High availability, dynamic tunneling, stealth (evading DPI and whitelisting mechanisms), secure delivery. Server 07 cannot be used for selling access directly; it is a gateway.
- Out-of-scope boundaries: Server 02 (decommissioned). Servers 08 and 10 (not owned, restricted use).

## Documentation
- **Architecture and Topology**: See [NETWORK_AND_ARCHITECTURE.md](./NETWORK_AND_ARCHITECTURE.md) for detailed IP mapping, server roles, and critical Xray core VLESS reverse proxy syntax requirements.

## Notes
The project is currently transitioning from manual management to an automated, documented system via the AI Brain.
