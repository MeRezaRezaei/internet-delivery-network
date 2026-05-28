# Decision Log

## Entries
- ID: D-001
- Date: 2026-05-19
- Decision: Formalized Xray v26 Simplified Reverse Proxy over XHTTP as the project standard for IDN tunnels.
- Rationale: Verified that this pattern provides the best balance of stealth (CDN obfuscation), performance (XHTTP mode packet-up), and configuration simplicity (Simplified Reverse Proxy syntax).
- Impact: All future tunnels MUST match `email` and `seed` fields between Portal and Bridge. Bridges MUST use `mode: "packet-up"` for CDN compatibility.
- Supersedes: None

- ID: D-002
- Date: 2026-05-21
- Decision: Server 07 (Portal) is restricted to Management and Staff traffic only.
- Rationale: Server 07 is the primary "Pro" exit point for the network. Exposing it to client traffic or anti-censorship payloads carries an unacceptable risk of losing our only reliable path out.
- Impact: No client-facing tunnels or proxies shall be deployed on Server 07. All client traffic must be routed via other Iranian nodes (e.g., Server 01, 03, 04) which are in turn bridged to external nodes.
- Supersedes: None

- ID: D-006
- Date: 2026-05-27
- Decision: Adopted Laravel 13 as the central management and orchestration framework for the IDN.
- Rationale: The project has outgrown manual script execution. Laravel provides a structured environment for managing the database, CLI commands, and eventually a web-based dashboard for network monitoring and tunnel management.
- Impact: All infrastructure files moved to `infra/`. Scripts are now executed through Artisan commands. Future logic will favor Eloquent models over flat-file management.

- ID: D-003
- Date: 2026-05-22
- Decision: Implemented 3-Port Deterministic Multicast formula with incremental/sequential database tunnel IDs (01-24), 3 outside servers (01-03), and direct plain-TCP peer-to-peer mesh routing over WireGuard.
- Rationale: Enables 100% collision-free, loop-free, and self-documenting port allocation where any port number clearly encodes its type, tunnel ID, outside server ID, inside server ID, and CDN ID. Bypassing port 443 SSL between mesh servers eliminates CPU overhead and connection handshake latency over the secure WireGuard private network.
- Impact: Derived ports: Type 1 (Reverse Tunnel): `10000 + (T*1000) + (O*100) + (I*10) + C`, Type 2 (User XTLS): `20000 + (T*1000) + (O*100) + (I*10) + C`, Type 3 (SOCKS Delivery): `30000 + (T*1000) + (O*100) + (I*10) + C`. Remote peers route directly to the target's derived plain-TCP port over WireGuard (e.g. target_ip:13221) rather than jumping through secondary SSL ports. Compiled 6 inside node configs with 2592 dynamic combinations each.
- Supersedes: None
- ID: D-004
- Date: 2026-05-22
- Decision: Integrated Direct Reverse Proxy routing (VLESS reverse tunnel over XHTTP) into the unified, replicated Xray configuration on all Marzban edge nodes, completely bypassing SOCKS5 bridging.
- Rationale: Eliminating the local SOCKS loopback hop (`Iran XTLS -> SOCKS Out -> Local SOCKS In -> Reverse Portal`) directly targets the registered reverse proxy outbound tag `reverse-out-{T}_{O}_{I}_{C}`. This reduces latency, saves CPU cycles, and simplifies the codebase. Gathering all portal inbound tags and adding them to the Marzban `exclude` variable prevents dynamic credential injection into these tunnel registration ports, keeping the configuration lightweight.
- Impact: Generated `configs/xray/generated/xray_unified.json` filtered to **384 active scenario combinations** (768 inbounds, 769 routing rules) across active outside servers ("01", "03"), active inside nodes ("01", "03", "04", "05"), and active CDNs ("01", "05"). Compiled `configs/xray/generated/exclude_tags.txt` and `configs/xray/generated/exclude_tags_csv.txt` containing 768 portal tags for Marzban.
- Supersedes: None

- ID: D-005
- Date: 2026-05-26
- Decision: Implemented programmatic TLS certificate generation and inline PEM list injection in VLESS Portal configurations.
- Rationale: Eliminates filesystem permission errors and Docker isolation mapping boundaries inside Xray/Marzban execution contexts. Formatting PEM outputs as single-element lists containing the entire multiline string bypasses strict parser padding/character concatenation aborts.
- Impact: Guaranteed out-of-the-box self-contained Portal configurations with native SSL terminated directly inside Xray.
- Supersedes: None

- ID: D-006
- Date: 2026-05-26
- Decision: Transitioned XHTTP/XMUX obfuscation padding to custom HTTP headers ("X-Padding") and lowered the request rotation quota to 1,000 requests.
- Rationale: Prevents CDNs from stripping query-based padding (which triggers Xray's `invalid padding length:0` crash). Enforcing `packet-up` mode on both Bridge and Portal aligns the path sequence parser and avoids `strconv.ParseUint` crashes. Lowering the XMUX request limit to 1,000 forces rapid connection rotation to evade timing analysis and reset GFW UDP QoS throttles.
- Impact: Solved active SplitHTTP reverse tunnel crashes and enabled resilient domestic/international CDN traversal.
- Supersedes: D-001 (Refined padding placement and modes)

- ID: D-007
- Date: 2026-05-27
- Decision: Deployed a containerized Xray API testing environment using `teddysun/xray` and Docker Compose.
- Rationale: Provides an isolated environment to test `HandlerService` and `StatsService` via gRPC without impacting the host or production services. Using Docker ensures consistent environment and easy cleanup.
- Impact: Port 10085 exposed for gRPC API; Port 10086 for simulated traffic. Config managed via `infra/configs/xray/test_api_config.json`.

