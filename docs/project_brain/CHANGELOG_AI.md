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

## 2026-05-20
- **Session Startup:** Loaded full project brain and re-synchronized session state.
- **Infrastructure Synchronization (srv07):** Updated management documentation to reflect the migration of Server 07 to a Docker-based stack:
    - **Marzban**: Deployed via Docker for advanced user and node management.
    - **MySQL**: Implemented as the backend database for Marzban.
    - **Technitium DNS**: Deployed via Docker to provide recursive DNS and blocking/filtering capabilities for the IDN.
- **Connectivity Analysis:** Identified and documented potential connectivity gaps following the infrastructure shift; prioritized remote health check refactoring.

