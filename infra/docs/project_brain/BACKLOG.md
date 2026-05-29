# IDN BACKLOG

## High Priority
- [ ] **IDN-060 Centrifugo Real-time Streaming**: Replace polling loops with WebSocket streams.
- [ ] **IDN-061 Native UUID Implementation**: Migrate users and subscriptions to native MySQL UUIDs.
- [ ] **IDN-064 Asymmetric Split-Routing**: Decouple Download and Upload paths with spoofed xHTTP aggregation.
- [ ] **IDN-062 Role-Based Node Topology**: Restrict node abilities to specific roles (Edge, Core, DNS).
- [ ] **IDN-065 Local Xray Mock Fleet**: Build `idn:mock:fleet` for testing local native Xray binaries.
- [ ] **IDN-063 Subscription Tiers API**: Build generic Xray subscription links with varied tier access levels.

## Done
- [x] **IDN-040 Advanced Routing Engine**: Generate Xray routing rules based on real-time node metrics.
- [x] **IDN-041 Multi-Node Batching**: Support provisioning a single tunnel across multiple hops (Chain) in one atomic transaction.
- [x] **IDN-042 TLS/XHTTP Integration**: Add support for modern Xray transports (XHTTP, Split-HTTP) in the Dashboard.
- [x] **IDN-018 Control Plane Foundation**: Signal/Log dispatchers, Node registry, Xray Protobuf integration.
- [x] **IDN-019 Centralized MySQL Config DB**: Database schema, Models, Node inventory seeding.
- [x] **IDN-034 Centralized IDN Dashboard**: UI for node fleet monitoring, tunnel management, and real-time log tailing.
- [x] **IDN-020 CLI Orchestrator**: `idn` CLI shortcut and fleet orchestration logic.
- [x] **Control Plane Hardening**: Transactional batching, Redis Streams, Filesystem verification.
- [x] **IDN-050 Automatic Failover**: Monitor node health and automatically re-route tunnels if a node goes down.
- [x] **IDN-052 Mobile Dashboard**: Responsive UI optimization for mobile management.
- [x] **IDN-051 Traffic Visualization**: Add Grafana-like charts to the Dashboard.

## Future Ideas
