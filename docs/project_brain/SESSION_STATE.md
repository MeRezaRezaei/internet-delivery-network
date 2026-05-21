# Session State

## Last Updated
- Date: 2026-05-20
- Owner: Gemini CLI

## Current Stage
- Stage: Operational Maintenance & Automation
- Focus: Integrating Docker-based orchestration (Marzban) and centralized DNS (Technitium) into the management framework.

## Done
- Mapped full 10-server topology and verified access paths (WSL -> 04 -> 07 -> 09/01).
- Deciphered the "Reverse-Reverse Proxy" trick (Iran as Portal, US as Bridge).
- Documented the "Multicast IDN" delivery model where the US Origin pushes traffic to Iranian Edge nodes.
- Recovered critical technical tips from a "broken" session log, specifically regarding Xray v26 Simplified Reverse Proxy.
- Standardized the architectural patterns in `NETWORK_AND_ARCHITECTURE.md` to align with Xray v26 (XHTTP, seed/email matching).
- **Incident Recovery (2nd Time):** Documented the second occurrence of a broken session. Re-synced state from the AI Brain to maintain continuity. Verified that no critical architectural data was lost due to the robust "AI Brain" documentation requirement.
- **Functional stabilization of the DE-08 tunnel:** Fixed email mismatch, standardized reverse tags, and bypassed CDN to achieve stable 21-08-07-05 tunnel registration. Verified traffic flow via SOCKS5 test.
- **Dual CDN Tunnels for Server 08:** Successfully established both ArvanCloud (`i-07.doctel.ir`) and Cloudflare (`i-07.menudigi.ir`) tunnels on Server 08.
- **Xray v26 Syntax Correction:** Discovered and documented that Xray v26 Simplified Reverse Proxy (VLESS) REQUIRES the simplified outbound syntax (no `vnext`) on the Bridge side.
- **HAProxy Path Routing:** Updated Server 07 HAProxy to route tunnel paths on both HTTP and HTTPS frontends for maximum CDN compatibility.
- **HTTPS Enforcement:** Updated Server 07 HAProxy to globally redirect HTTP (port 80) to HTTPS (port 443), ensuring all CDN traffic is encrypted.
- **Architectural Documentation:** Created `TOOLSET_ORCHESTRATION.md` to permanently document the relationships between Marzban, Xray, HAProxy, and CDNs, including Xray v26 specific requirements.
- **Verification:** Confirmed internet access via both tunnels using SOCKS5 ports 21080 (Arvan) and 21081 (Cloudflare) on Server 07.
- **Cloudflare Proxy Verification:** Confirmed that `i-07.doctel.ir` (Cloudflare/ArvanCloud) correctly routes traffic to Server 07 HAProxy and then to Xray backends. Verified active sessions on tunnel `23-01-07-05`.
- **Topology Documentation:** Created `docs/TOPOLOGY.md` mapping the node relationships and traffic flow.
- **Credential Autonomy:** Successfully established SSH access to Server 07 using documented credentials (`merezarezaei`/`asdfjkl`) and verified `id_rsa_idn` key usage from srv09.
- **Live Investigation (srv07):** Successfully logged into Server 07 and discovered the exact configurations for the new stack:
    - **Marzban**: Running in Docker (network: host) with SQLALCHEMY mapping to local MySQL.
    - **MySQL**: Native Systemd service with dedicated `marzban` user.
    - **Technitium DNS**: Native Systemd service acting as the recursive resolver.
    - **Xray Integration**: Mapped XTLS/XHTTP inbounds (5011, 5012) and their corresponding SOCKS outbounds (21081, 24081).
- **Credential Recovery:** Captured MySQL and Marzban Admin credentials from Server 07 `.env` files.
- **Infrastructure Shift (2026-05-20):** Recorded the migration of Server 07 to a Docker-based stack. Verified that Technitium DNS and MySQL are currently running natively while Marzban is containerized.
- **Server 10 Tunnel Establishment (2026-05-20):** 
    - Successfully established the srv10-srv07 tunnel using path `/24-10-07-06/xtls` (srv08 pattern).
    - Port Mappings: Portal XTLS Port `5013`, Portal SOCKS Port `21010`.
    - Integrated tunnel into Marzban `xray_config.json` on srv07 as a Reverse Portal.
    - Updated srv07 HAProxy to route path `/24-10-07-06/xtls` to the new portal backend.
    - Verified direct Wireguard management path to srv10 at `10.1.0.4`.
- **Xray-core Deep Investigation (2026-05-20):**
    - Completed a massive reverse-engineering effort of the Xray-core codebase.
    - Created a permanent technical reference database in `docs/project_brain/xray_reference/`.
    - Documented ARCHITECTURE_AND_LOGIC, API_AND_PROTOBUF_MODELS, NEW_FEATURES_DEEP_DIVE (XHTTP, REALITY, Reverse), and a DEVELOPER_GUIDE.
    - Extracted exact protobuf models and handshake logic for REALITY and XHTTP modes.
- **Marzban Path Isolation Fix (2026-05-21):**
    - Resolved the critical bug where `/m` and `/m7` were showing the same panel.
    - Implemented a robust "Isolation Engine" in HAProxy using domain-agnostic `Referer` matching.
    - Added automatic trailing slash redirects to ensure consistent pathing and referer generation.
    - Verified that root paths like `/dashboard`, `/api`, and `/statics` are correctly routed to their respective backends (Main vs PUBG) based on the entry point.
- **HAProxy Refactor & Bug Fixes (2026-05-21):**

    - Refactored all HAProxy backend names on srv07 to a readable `bk_srvXX_vless/xtls` format.
    - Fixed a critical routing bug for Server 10: aligned HAProxy path `/24-10-07-06` with Xray config.
    - Added the missing XTLS backend for srv10 on port 5013.
    - Cleaned up the `is_tunnel` ACL in the HTTP frontend to ensure reliable redirection.
    - Standardized Marzban backend names to `bk_marzban_main` (port 2020) and `bk_marzban_pubg` (port 8002).
- **Session Startup (2026-05-20):** Re-verified Server 07 connectivity and port 21080 tunnel functionality. Identified that `idn-health-check.sh` requires updates for remote execution and correct port mapping.


- **Marzban Migration (2026-05-21):**
    - Successfully migrated the 400-user `pubg` Marzban panel from Server 03 to Server 07.
    - Server 07 is now the primary orchestrator, managing nodes on srv01, srv03, and srv04.
    - Implemented path-based routing in srv07 HAProxy:
        - Migrated panel (400 users): Root paths (`/dashboard`, `/sub`, etc.).
        - Legacy panel (5 users): Prefixed path (`/m7/`).
    - Decommissioned legacy panels on Server 03 to resolve node conflicts.
    - Verified zero-downtime transition for existing 400+ user subscriptions.

## Not Done
- Automated health checks for the various VLESS tunnels.
- Centralized management of Xray configs (currently scattered across nodes).

## Immediate Next Objective
- **Fix/Automate Health Checks:** Update `idn-health-check.sh` to handle remote execution and ensure it correctly validates all tunnels listed in `NETWORK_AND_ARCHITECTURE.md`.
- Implement a cron job or background service for continuous monitoring.


## Known Constraints
- Access to most nodes requires jumping through Server 04.
- Xray version v26+ syntax is mandatory for the modern Simplified Reverse Proxy logic.
- ArvanCloud CDN requires `packet-up` mode for reliable XHTTP streaming.
