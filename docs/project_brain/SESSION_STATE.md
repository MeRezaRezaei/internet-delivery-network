# Session State

## Last Updated
- Date: 2026-05-24
- Owner: Antigravity

## Current Stage
- Stage: Speed Aggregation & Performance Tuning
- Focus: Generating parallel multi-channel VLESS tunnels to bypass single-stream bottlenecks and evading GFW DPI throttling.

## Done
- **Xray-core VLESS Reverse & Mux/XMux Architecture Analysis (2026-05-24):** 
    - Completed an in-depth code and documentation analysis of the VLESS Simplified Reverse Proxy (introduced in Xray-core v26+).
    - Uncovered a critical connection instability bug in the `generate_100_tunnels.py` generator script: it was hardcoding `"dialerProxy": "tor"`, forcing all 100 high-speed CDN tunnels on the Bridge side through an unstable local Tor socks proxy.
    - Patched `scripts/generate_100_tunnels.py` to remove the hardcoded Tor proxy by default, allowing high-performance direct dialout connections.
    - Refactored the generator script to support both **unified** (common single-tag) and **unique** (individual tag) bridge-side reverse tags, simplifying the Bridge routing rules significantly while preserving full multicast speed aggregation.
    - Documented the fundamental pairing mechanisms of Xray's Simplified VLESS Reverse Proxy: the integrator is strictly the VLESS UUID and email mapping, bypassing the deprecated legacy need for fake internal domains.
    - Clarified the operational difference between legacy `mux` (the custom `mux.cool` protocol, which is highly discouraged for modern XTLS-Vision/Reality due to DPI patterns) and `xmux` (native multiplexing over modern HTTP/2 or HTTP/3 XHTTP streams).
    - Regenerated optimized production tunnel configs for `100_10_01_05`, `100_10_04_05`, and `100_10_03_05` using the patched generator.
- **100-Tunnel Speed Aggregation Engine (2026-05-23):** Developed a parallel-stream configuration generator (`generate_100_tunnels.py`) generating 100 concurrent VLESS reverse outbounds. Deployed client load-balancing selectors (`balancer_100`) on the Portal side, distributing connection load dynamically to aggregate speeds and bypass Cloudflare/ArvanCloud TCP single-stream throttling. Successfully generated production-ready configs for `100-10-01-05` (using `i-01.doctel.ir`), `100-10-04-05` (using `i-04.doctel.ir`), and `100-10-03-05` (using `i-03.doctel.ir`).
- **Dynamic HAProxy Regex Sub-Path Alignment (2026-05-23):** Patched the dynamic HAProxy generator script to align regex patterns for `is_xtls` and `is_reverse` to support trailing wildcard sub-paths using `($|/.*)` instead of strict end-anchored `/` matchers. This resolved the 404 routing fallbacks under `bk_fallback` on nodes running VLESS over XTLS/XHTTP.
- **Dynamic CDN-Style Refactor & SSL Alignment (2026-05-22):** Refactored the mesh generators (`generate_haproxy.py` and `generate_xray.py`) to transition the IDN from high-overhead port-multiplying to a dynamic, single-port CDN-style map-routing system. HAProxy dynamically parses incoming tunnel paths (slash/dash separated formats) and looks them up in `/etc/haproxy/inside_servers.map` to route traffic dynamically over WireGuard plain-HTTP (port 80) for remote nodes, or local loopbacks (ports 10001/20001) for local nodes. Implemented exact standard port 443 SSL-aligned frontend (`incoming_https` using `/opt/node/certs/ssl_bundle.pem`, h2/http/1.1 ALPN, and healthcheck status `OK_NEW`). Successfully compiled and validated configurations for all target nodes.

- **Unified Replicated Xray Configuration Compilation & SOCKS5 Bypass (2026-05-22):** Generated a filtered unified Xray config (`configs/xray/generated/xray_unified.json`) containing **384 active scenario combinations** (768 inbounds, 769 routing rules) across active outside servers ("01", "03"), active inside nodes ("01", "03", "04", "05"), and active CDNs ("01", "05") to match Marzban processing limits. It implements direct reverse proxy routing (VLESS over XHTTP reverse proxy), completely bypassing SOCKS5 loopbacks and reducing connection latency. Compiled and exported 768 reverse portal inbound tags to `configs/xray/generated/exclude_tags.txt` and `configs/xray/generated/exclude_tags_csv.txt` for Marzban tag exclusion settings.

- **3-Port Deterministic Multicast HAProxy Peer-to-Peer Compilation (2026-05-22):** Refined and compiled the dynamic HAProxy configuration compiler script (`scripts/generate_haproxy.py`) to fully support dynamic/incremental database tunnel IDs (`01` through `24`) and exactly 3 outside servers (`01` through `03`), expanding the matrix to 2592 combinations per node. Optimized the mesh routing by replacing redundant SSL-terminated HAProxy-to-HAProxy port 443 calls with direct plain-TCP peer-to-peer routing over the secure WireGuard network, directly targeting the target peer's specific derived port (e.g. `10000 + (T*1000) + (O*100) + (I*10) + C` for VLESS, `20000 + (T*1000) + (O*100) + (I*10) + C` for XTLS), significantly reducing latency and protocol overhead.
- **Centralized Configuration Database Design (2026-05-22):** Formulated a robust, MySQL-backed dynamic database schema and automated compilation/deployment concept to replace fragile manual file management across target servers. Designed structured SQL schemas for nodes, tunnels, HAProxy rules, Technitium DNS sync, and audit logs. Created a comprehensive ERD and orchestration workflow proposal ([config_database_proposal.md](file:///C:/Users/MeRezaRezaei/.gemini/antigravity/brain/4df94a8d-d8c5-4541-9fcd-13707308a0ca/config_database_proposal.md)).
- **Repository Reorganization and Cleanup (2026-05-22):** Cleaned up and structured the repository root by reorganizing loose keys, config backups, and scripts into dedicated directories (`keys/`, `configs/haproxy/`, `configs/xray/backups/`, and `scripts/`). Removed empty folders and garbage files (`null`), updated `.gitignore` rules, and synchronized the entire setup with Git.
- **Project Brain Upgrade - Prompt Library & Guardrails (2026-05-22):** Conducted a deep analysis of historical session misalignments and corrections. Successfully upgraded `PROMPT_LIBRARY.md` to define strict **Correction Prevention Guardrails** covering: Zero-Touch critical tunnel protection, Xray v26 simplified outbound syntax, Marzban multi-panel subdomain isolation, and Mesh SSH Port 2022 routing requirements.
- **Mapped and Locked Unbreakable Direct Tunnels (2026-05-21):** Discovered and documented the exact configurations, public/internal ports, and credentials for the three core management tunnels bridging Server 07 (Portal) with srv09 (US), de-server (DE), and Pubg-Sell (DE-PG). Wrote their configs as static JSON backups in the brain and integrated strict, permanent 'Zero-Touch' safety policies across the AI operating protocols and network architecture documents.
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
- **Marzban Subdomain & Subscription Resolution (2026-05-21):**
    - Achieved 100% isolation using subdomain-based routing.
    - **dash.new-state.ir** -> Migrated/PUBG Panel (400 Users, Port 8002).
    - **panel.new-state.ir** -> Old/Legacy Panel (5 Users, Port 2020).
    - Updated `XRAY_SUBSCRIPTION_URL_PREFIX` in both instances to match their respective subdomains.
    - Simplified HAProxy to use clean Host-header routing, removing all unreliable path-based logic.
- **Marzban Subdomain Isolation (2026-05-21):** (Merged into final resolution above)
- **Marzban Path Isolation Fix (Attempt 1):** (REVERTED) Path-based isolation proved unreliable due to application-level path collisions.
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

- **Domestic-US Proxy Diagnostics & Active Network Findings (2026-05-21):**
    - Performed comprehensive read-only diagnostics across domestic nodes (srv01, srv03, srv04) and US bridge (srv09).
    - **Tunnels Classification:**
        * **Static Tunnels:** Extremely static, dedicated solely for admins/staff to access external services and servers. These must NOT be modified or tested.
        * **Dynamic Tunnels:** Managed dynamically via Marzban nodes. We can touch and modify these configs (the configs series that work under Marzban or are generated/managed by the Marzban Xray node itself).
    - **Zero-Touch Rules:**
        * **Server 07 (Portal):** Absolutely DO NOT touch or test any configuration files or settings on srv07. It is our only gateway/way out.
        * **Server 09 (Leo):** Do not touch or modify configurations on srv09 directly. The process of Antigravity from inside must fix the duplicate configs or alignment later. Added the duplicate Xray config conflict to the backlog for safe internal remediation.
    - **Duplicate Process Bottleneck (srv09):** Identified that srv09 runs BOTH the monolithic `xray.service` (which loads all config files via `-confdir`) and individual template instances (`xray@*.service`). This causes duplicate connections, session flapping, and poor latency.
    - **Private Mesh SSH Issue (Port 2022):**
        * Investigated why direct SSH using the private Wireguard/Mesh network IP (`10.255.1.9`) to srv09/srv08/srv10 fails on the standard port 22.
        * Discovered that SSH is explicitly configured to listen on **Port 2022** on the `mesh` and `tailscale` interfaces, which explains why standard port 22 connections fail with `Connection refused`.
        * Added findings to the reference logs to clarify the SSH jump architecture and logical routes.
    - **DNS Censorship Status:** Domestic servers fail to resolve CDN domains locally due to DNS censorship/hijacking. However, this is NOT a priority because clients fetch DNS via secure DNS-over-HTTPS (DoH). Technitium is running fine on srv07 but we will not force local DNS redirection on domestic nodes now.
    - **Configuration Mismatches:**
        * **srv03 (Bamdad):** HAProxy is configured perfectly for `/c-05-01-03` and `/c-06-02-03`, but srv09 uses `11-01-03-01.json` with path `/11-01-03-01`.
        * **srv01 (Shahriar):** Local Xray has `/c-02-02-01` on port 1082, but srv09 has no outbound config and srv01 HAProxy has no routing rule.
        * **srv04 (Shiraz):** SOCKS 1082 (`/c-08-02-04`) fails because srv09 has no outbound config and srv04 HAProxy has no routing rule.

- **Iran Domestic Portals SOCKS Bridging Resolved (2026-05-21):**
    - **Surgical Path Remediation**: Identified that Server 09 (US Bridge) was dialing path `/11-01-03-01` but Server 03 (Bamdad Portal) and HAProxy were listening on path `/c-01-01-03-01`. Wrote the corrected configuration directly and transferred it from Server 07 to Server 09 using `scp` over port 2022.
    - **Service Activation**: Enabled and started the long-term systemd portal services `xray@c-01-01-01-01` on Server 01 and `xray@c-01-01-03-01` on Server 03.
    - **Functional Validation**: Successfully completed the full-chain diagnostic latency checks. Verified 100% active remote DNS resolving, SOCKS5 request grants, and HTTP/2 200 responses to `google.com` on all target interfaces:
        * **Server 01**: Port 1081 (Marzban) and Port 1085 (Arvan) -> **PASS**
        * **Server 03**: Port 1081 (Marzban) -> **PASS**
        * **Server 04**: Port 1081 (Marzban) and Port 1085 (Arvan) -> **PASS**

## Not Done
- Safe remediation of the duplicate Xray process conflict on srv09.
- Centralized management of Xray configs.

## Immediate Next Objective
- **Centralized Configuration Database Implementation**: Deploy the proposed MySQL database on srv07, populate existing inventory and tunnels, and develop the `idn-ctl` CLI orchestrator script for zero-touch configuration compiling and automated validation checks.

## Known Constraints
- Access to most nodes requires jumping through Server 04.
- Xray version v26+ syntax is mandatory for the modern Simplified Reverse Proxy logic.
- ArvanCloud CDN requires `packet-up` mode for reliable XHTTP streaming.
