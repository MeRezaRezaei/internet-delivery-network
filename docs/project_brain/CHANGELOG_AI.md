# AI Changelog

## 2026-05-19
- **Bootstrap:** Initialized project brain docs from template.
- **Context:** Established 'Internet Delivery Network' as the placeholder product name.
- **Discovery:** 
    - Mapped 10-server topology and confirmed multi-hop access paths.
    - Verified "Reverse-Reverse Proxy" architecture (Iran=Portal, US=Bridge).
    - Documented "Multicast IDN" delivery model.
    - Updated `NETWORK_AND_ARCHITECTURE.md` with technical syntax for Xray-core v1.8.0+.
- **Recovery:** 
    - Retrieved and analyzed "broken" session logs.
    - Identified critical Xray v26 "Simplified Reverse Proxy" standards (email/seed matching, XHTTP `packet-up`).
    - Formalized v26 architectural patterns in the brain documentation.
- **Cloudflare Verification:**
    - Verified that `i-07.doctel.ir` (Cloudflare/ArvanCloud) correctly routes traffic to Server 07.
    - Confirmed HAProxy on Server 07 forwards path-based tunnels (e.g., `/23-01-07-05`) to Xray backends.
    - Validated active sessions on the US Bridge tunnel using HAProxy stats.
- **Dual CDN Tunnels (Server 08):**
    - Implemented and stabilized ArvanCloud and Cloudflare tunnels on Server 08.
    - Identified Xray v26 requirement for simplified outbound syntax in VLESS Reverse Proxy.
    - Added path-based routing to Server 07 HAProxy HTTP frontend.
    - Verified dual internet delivery to Server 07 via SOCKS5 tests.
- **Topology & Documentation:**
    - Created `docs/TOPOLOGY.md` to map node relationships, IPs, and traffic flows.
    - Updated project memory with SSH credentials and network roles.
- **Operational Autonomy:**
    - Established autonomous SSH connection to Server 07 via mesh network using `merezarezaei` user and documented passwords.

## 2026-05-21
- **Marzban Migration:**
    - Migrated the 400-user `pubg` panel from Server 03 to Server 07.
    - Integrated the migrated panel into Server 07's native MySQL.
    - Refactored Server 07 HAProxy for dual-panel orchestration:
        - Primary (400 users) on root paths.
        - Legacy (5 users) on `/m7/` path with transparent rewriting.
    - Decommissioned srv03 panels and verified srv03 node connectivity to srv07.
    - Confirmed srv01 and srv04 node stability from the new central hub.
    - Stored all migration backups on the US management server.
- **Session Startup:** Loaded full project brain and re-synchronized session state.
- **Infrastructure Synchronization (srv07):** Updated management documentation to reflect the migration of Server 07 to a Docker-based stack:
    - **Marzban**: Deployed via Docker for advanced user and node management.
    - **MySQL**: Implemented as the backend database for Marzban.
    - **Technitium DNS**: Deployed via Docker to provide recursive DNS and blocking/filtering capabilities for the IDN.
- **Connectivity Analysis:** Identified and documented potential connectivity gaps following the infrastructure shift; prioritized remote health check refactoring.
- **Marzban Subdomain & Subscription Fix:**
    - Finalized strict isolation using `dash.new-state.ir` (PUBG/8002) and `panel.new-state.ir` (Main/2020).
    - Updated application-level `XRAY_SUBSCRIPTION_URL_PREFIX` to align with the new subdomains.
    - Verified clean Host-header routing in HAProxy, eliminating path and referer collisions.
- **Marzban Subdomain Isolation:** (Merged into final fix above)
- **Marzban Isolation Fix (Attempt 1):** (FAILED/REVERTED) Referer-based path isolation failed due to application root path collisions.
    - Refactored Server 07 HAProxy backend naming to human-readable `bk_srvXX_vless/xtls` format.
    - Fixed Server 10 routing bug (aligned path to `/24-10-07-06`) and added port 5013 backend.
    - Standardized Marzban panel naming to `bk_marzban_main` and `bk_marzban_pubg`.
    - Verified live traffic routing via HAProxy logs.
- **US Proxy & Domestic Nodes Connectivity Diagnostics:**
    - Performed comprehensive, read-only analysis of srv09 (US Bridge) and domestic nodes.
    - Mapped inside-to-inside connectivity: Verified 0% packet loss and low latency (<15ms) on private mesh network.
    - Classified network tunnels into **Static Tunnels** (admin/staff access, zero-touch) and **Dynamic Tunnels** (Marzban-managed nodes, touchable).
    - Identified a critical duplicate process bottleneck on srv09 (monolithic vs templated `xray` services running simultaneously).
    - Mapped the private Mesh SSH interface: Found SSH is listening on **Port 2022** on the Mesh and Tailscale interfaces of srv09/srv08/srv10, causing standard port 22 connections to fail with `Connection refused`.
    - Documented these findings permanently in the AI Brain for future internal remediation.
- **Unbreakable Direct Tunnels Isolation & Safety Enforcement:**
    - Connected to srv09 (US), de-server (DE), and Pubg-Sell (DE-PG) to extract exact configurations for the three direct management tunnels.
    - Wrote physical static JSON backups for each of the three portal/bridge pairs inside `docs/project_brain/static_unbreakable_tunnels/`.
    - Codified a strict, permanent **Zero-Touch Constraint Policy** inside `OPERATING_PROTOCOL.md`, `NETWORK_AND_ARCHITECTURE.md`, and `OPERATIONAL_SAFETY.md` to protect these tunnels from any edits, restarts, or interruptions.



