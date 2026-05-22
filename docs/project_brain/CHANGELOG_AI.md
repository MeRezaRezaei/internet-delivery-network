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
- **Iran Domestic Portals SOCKS Tunnels Stabilization (2026-05-21):**
    - **Server 03 Path Alignment**: Fixed the path mismatch on Server 09 (US Bridge) for Server 03 from `/11-01-03-01` to `/c-01-01-03-01` to match Server 03's HAProxy path rule and xray portal config. Executed configuration file transfer via `scp` from Server 07 to Server 09 over port 2022.
    - **Server 01 Naming Streamlining**: Cleaned up the redundant `c-` prefix from Server 01. Renamed configuration files to `01-01-01-01.json` and `01-01-01-05.json`, stripped the prefix from path settings, and updated the HAProxy config using a precise global `sed` replacement (with validation passing cleanly). Discarded old services and enabled/started `xray@01-01-01-01` and `xray@01-01-01-05`.
    - **Server 09 Bridge Alignment**: Updated the `01-01-01-01.json` config on Server 09 to target the correct Marzban domain address `i-01.docreverse.ir` (instead of the obsolete `i-01.m3600.ir`) and updated paths to remove the redundant `c-`.
    - **Domestic Services Launch**: Enabled and started systemd portal services natively (`xray@c-01-01-03-01` on Server 03).
    - **100% SOCKS Verification**: Ran complete diagnostic latency suite. Confirmed active SOCKS5 proxy internet access on Server 01 (1081/1085), Server 03 (1081), and Server 04 (1081/1085) with remote DNS resolving successfully.

## 2026-05-22
- **Repository Reorganization and Cleanup:**
    - Cleaned up and structured the repository root by reorganizing loose keys, config backups, and scripts into dedicated directories.
    - Created `keys/` and moved WireGuard and SSH keys into it.
    - Created `configs/haproxy/` and moved HAProxy backup configurations into it.
    - Created `configs/xray/backups/` and moved Xray/Marzban JSON config backups into it.
    - Created `scripts/` and `scripts/scratch/` and moved all diagnostic and scratch scripts into them.
    - Cleaned up empty folders and garbage files (`null`).
    - Updated `.gitignore` rules to prevent future file clutter.
    - Synchronized all structural changes in Git tracking.
- **Project Brain Upgrade - Prompt Library & Guardrails:**
    - Conducted a thorough analysis of past session misunderstandings and user corrections.
    - Upgraded `PROMPT_LIBRARY.md` to establish strict **Correction Prevention Guardrails** targeting four core historical risks: Zero-Touch management tunnel protection, Xray v26 simplified outbound syntax, Marzban multi-panel subdomain isolation, and Mesh SSH Port 2022 routing parameters.
- **Centralized Configuration Database Design:**
    - Drafted a comprehensive, MySQL-backed dynamic database schema design to replace fragile manual file editing across target nodes.
    - Structured Relational tables for nodes, tunnels, HAProxy rules, Technitium DNS sync, and audit logs.
    - Designed a safety-first deployment and validation orchestration pipeline concept ([config_database_proposal.md](file:///C:/Users/MeRezaRezaei/.gemini/antigravity/brain/4df94a8d-d8c5-4541-9fcd-13707308a0ca/config_database_proposal.md)) preventing configuration crashes or management lockouts.
    - Added the DB implementation and CLI orchestrator developer tasks to the backlog.
- **3-Port Deterministic Multicast HAProxy Peer-to-Peer Compiler Integration & Refinement:**
    - Modified the automated HAProxy configuration generator (`scripts/generate_haproxy.py`) to fully support dynamic/incremental database-mode tunnel IDs (`01` through `24`) and exactly 3 outside servers (`01` through `03`), expanding the compiled path matrix to 2592 combinations per node.
    - Designed and implemented three strictly non-overlapping, human-readable 5-digit TCP port derivation formulas:
        - **Type 1 (Bridge-to-Portal / Reverse Tunnel Port)**: `10000 + (T * 1000) + (O * 100) + (I * 10) + C` (listening range `11111`-`34366`).
        - **Type 2 (User XTLS Proxy Port)**: `20000 + (T * 1000) + (O * 100) + (I * 10) + C` (listening range `21111`-`44366`).
        - **Type 3 (SOCKS Delivery Port)**: `30000 + (T * 1000) + (O * 100) + (I * 10) + C` (listening range `31111`-`54366`).
    - Optimized the internal mesh routing by replacing redundant SSL-terminated HAProxy-to-HAProxy port 443 connections with direct, high-performance plain-TCP routing over the WireGuard secure private network (`10.255.1.x`) targeting the target peer's specific derived port (e.g., target_ip:13221), bypassing secondary SSL handshake overhead completely.
    - Successfully compiled and verified optimized configs for all 6 target inside nodes (`01` through `06`) under Git tracking.
- **Unified Replicated Xray Configuration Compiler & SOCKS5 Bypass:**
    - Executed `scripts/generate_xray.py` compiler to generate the unified Xray JSON configuration (`configs/xray/generated/xray_unified.json`) representing 2592 scenario combinations.
    - Implemented a Direct Reverse Proxy routing pattern mapping `IN_{T}_{O}_{I}_{C}_XTLS` directly to the portal's reverse outbound tag (`reverse-out-{T}_{O}_{I}_{C}`), completely eliminating SOCKS5 local loopbacks and double-encryption overhead.
    - Structured the 5184 inbounds (2592 user-facing XTLS on Type 2 ports and 2592 Reverse Portals on Type 1 ports) to share the exact same configuration on all nodes, allowing dynamic registration without port conflicts.
    - Compiled and exported all 2592 Reverse Portal inbound tags to `configs/xray/generated/exclude_tags.txt` and `configs/xray/generated/exclude_tags_csv.txt` for integration into the Marzban dynamic exclude tag variable.




