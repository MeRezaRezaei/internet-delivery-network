# IDN BACKLOG

## High Priority
- [x] **IDN-040 Advanced Routing Engine**: Generate Xray routing rules based on real-time node metrics. (Merged via US node)
- [x] **IDN-041 Multi-Node Batching**: Support provisioning a single tunnel across multiple hops (Chain) in one atomic transaction. [COMPLETED] (Merged via DE node)
- [x] **IDN-042 TLS/XHTTP Integration**: Add support for modern Xray transports (XHTTP, Split-HTTP) in the Dashboard. [COMPLETED] (2026-05-30)
- [x] **IDN-045 Automated Connectivity Tests**: Implement a command to verify the 5NF-linked tunnels using `xray -test` and live pings. [COMPLETED] (2026-05-30)
- [x] **IDN-046 Dashboard Integration for Tunnel Verification**: Add a "Verify" button to the Tunnel management UI that triggers the connectivity test via API. [COMPLETED] (2026-05-30)
- [x] **IDN-047 Fleet Reconciliation Engine**: Implement a command to sync and reconcile node health status between DB and Redis. [COMPLETED] (2026-05-30)

## Done
| ID | Priority | Title | Status | Depends On | Done When |
|---|---:|---|---|---|---|
| IDN-047 | P1 | Fleet Reconciliation Engine | done | IDN-019 | Command `idn:fleet:reconcile` implemented and verified |
| IDN-046 | P1 | Dashboard Integration for Tunnel Verification | done | IDN-045 | "Verify" button added to UI and functional via API |
| IDN-045 | P1 | Automated Connectivity Tests | done | IDN-043 | Command `idn:verify-tunnels` implemented and verified |
| IDN-043 | P1 | Model Unification (5NF -> IDN) | done | IDN-033 | Tunnel model linked to XrayInbound/Outbound IDs |
| PB-001 | P0 | Bootstrap project brain | done | - | Core docs filled and validated |
| IDN-018 | P1 | Control Plane Foundation | done | - | Signal/Log dispatchers, Node registry, Xray Protobuf integration |
| IDN-019 | P1 | Implement Centralized MySQL Config DB | done | IDN-018 | 5NF Relational schema and Laravel models implemented |
| IDN-034 | P2 | Centralized IDN Dashboard | done | IDN-033 | UI for node fleet monitoring, tunnel management, and real-time log tailing |
| IDN-020 | P1 | Develop CLI Orchestrator | done | IDN-019 | `idn` CLI shortcut and fleet orchestration logic |
| IDN-037 | P1 | Integrate Tailscale API | done | - | Tailscale status mapped to Node registry |
| IDN-038 | P0 | Validated Xray Relational Orchestrator | done | IDN-019 | renderer and native validator with mission-based API |
| IDN-039 | P1 | Technitium DNS Integration | done | - | Dashboard control for ad-blocking and record sync |
| IDN-040 | P1 | Automated Failover Engine | done | - | Tunnel migration logic on node offline detection |
| IDN-001 | P0 | Verify SSH Access to Server 07 | done | - | Can reliably SSH into Server 07 from WSL |
| IDN-002 | P0 | Verify Access to Server 09 (US) | done | IDN-001 | Can reach US server via Tailscale from Server 07 |
| IDN-003 | P1 | Map and Verify "Reverse-Reverse" Tunnel | done | IDN-002 | Full traffic path (01 -> 07 -> 09 -> Internet) verified |
| IDN-004 | P1 | Document Multicast Delivery Model | done | IDN-003 | IDN architecture fully documented in brain |
| IDN-005 | P1 | Stabilize Server 07-08 Tunnel | done | IDN-004 | Tunnel 21-08-07-05 is active and passing traffic |
| IDN-006 | P1 | Verify Cloudflare Proxy to srv07 | done | IDN-005 | Domain i-07.doctel.ir confirmed reaching srv07 Xray |
| IDN-007 | P0 | Verify Internet Delivery to srv07 | done | IDN-006 | srv07 can reach google.com via the reverse tunnel |
| IDN-008 | P2 | Automated Health Checks | done | IDN-007 | Script exists to verify all tunnel statuses |
| IDN-009 | P1 | Establish Server 10-07 Tunnel | done | IDN-008 | Tunnel active on port 21010 (Verified path /24-10-07-06) |
| IDN-010 | P0 | Xray-core Deep Investigation | done | - | Technical reference database created in xray_reference/ |
| IDN-011 | P0 | Migrate Marzban (srv03 -> srv07) | done | IDN-009 | Server 07 is orchestrator; srv03 is node; subs preserved |
| IDN-012 | P1 | HAProxy Naming Refactor & Bug Fixes | done | IDN-011 | Backends renamed; srv10 path fixed; port 5013 added |
| IDN-013 | P0 | Diagnose Domestic US Proxy Bottlenecks | done | IDN-012 | Root causes and connectivity parameters mapped |
| IDN-014 | P1 | Remediate srv09 Xray Conflicts & Align SSH | done | IDN-013 | Monolithic service stopped, template active; Mesh SSH verified |
| IDN-015 | P0 | Establish Stable SOCKS Outbounds on Domestic Nodes | done | IDN-014 | Verified active SOCKS connections on srv01, srv03, srv04 |
| IDN-016 | P1 | Reorganize and Clean Up Repository Root | done | - | Files sorted into keys/, configs/, scripts/ and tracked |
| IDN-017 | P1 | Analyze Misunderstandings & Upgrade Brain with Prompts | done | IDN-016 | Prompts created in PROMPT_LIBRARY.md to block critical pitfalls |
| IDN-021 | P1 | Multicast Config Generator Alignment | done | IDN-017 | Realigned HAProxy config generator to user's 6-node matrix and compiled all configs |
| IDN-022 | P0 | Unified Xray Config Compilation | done | IDN-021 | Compiled unified, replicated 2592 scenario Xray JSON config bypassing SOCKS5 |
| IDN-024 | P0 | Patched HAProxy Regex Sub-Paths | done | IDN-023 | Aligned HAProxy regex filters to match nested path routes and sub-paths |
| IDN-025 | P0 | 100-Tunnel Generator Deployment | done | IDN-024 | Created dynamic generator producing Bridge and Portal 100-channel config outputs |
| IDN-026 | P0 | Observatory & isolated UUIDs in 100-Tunnel Generator | done | IDN-025 | Generator updated with deterministic unique UUIDs, Observatory, and leastPing balancer |
| IDN-027 | P0 | Empirically Analyze High-Concurrency VLESS Reverse Proxy & Client-Mux | done | IDN-026 | Run loopback simulation, prove traffic demultiplexing, and output analysis |
| IDN-028 | P0 | Research and Analyze XHTTP & XMUX Deep-Dive Protocols | done | IDN-027 | Study Xray H2/H3 evasion, UDP encapsulation, and single-tunnel vs pool aggregation |
| IDN-029 | P0 | Create CDN-Optimized Direct VLESS Reverse Bridge Config | done | IDN-028 | Write high-performance TLS H2 Bridge config with Tor dialer |
| IDN-030 | P0 | Restructure Project for Laravel | done | - | All infra files in infra/, root clean for Laravel |
| IDN-031 | P0 | Install Laravel 13 with PHP 8.5 | done | IDN-030 | Laravel functional in root with PHP 8.5 |
| IDN-032 | P1 | Integrate IDN Scripts as Artisan Commands | done | IDN-031 | Scripts accessible via php artisan idn:* |
| IDN-033 | P0 | Develop Xray-core gRPC API Laravel Integration | done | IDN-031 | Native Facade Xray:: with multi-core support |
| IDN-036 | P0 | Dockerize Development Environment | done | - | Laravel, Xray, and multi-core setup running in Docker |
| IDN-030 | P0 | Create CDN-Optimized Direct VLESS Reverse Portal Config | done | IDN-029 | Design and compile Portal configuration with native port 443 TLS termination |
| IDN-031 | P0 | Establish and Verify High-Obfuscation SplitHTTP VLESS Reverse Tunnel | done | IDN-030 | Establish test VLESS tunnel over ArvanCloud CDN to Server 01 and measure latency/success |
| IDN-032 | P0 | Patch 100-Tunnel Generator with Persistence, Fast Connection Rotation, and Debug logging | done | IDN-026 | Generator supports full UUID/SSL reuse, rotates connections after 1k requests, and sets loglevel debug |
| IDN-033 | P0 | Deploy and Validate Server 04 CDN Loopback control configs | done | IDN-032 | portal_cdn_loopback and bridge_cdn_loopback successfully staging-staged and validated with xray -test on Server 04 |
| IDN-034 | P0 | Execute and Verify Server 04 local loopback tunnel | done | IDN-033 | VLESS reverse tunnel successfully registered and traffic routed locally |
| IDN-035 | P0 | Setup Xray API Test Environment | done | - | docker-compose.yml and test_api_config.json created and container started |

## Future Ideas
- [x] **IDN-050 Automatic Failover**: Monitor node health and automatically re-route tunnels if a node goes down. (Completed previously)
- [x] **IDN-051 Traffic Visualization**: Add Grafana-like charts to the Dashboard.
- [x] **IDN-052 Mobile Dashboard**: Responsive UI optimization for mobile management.
