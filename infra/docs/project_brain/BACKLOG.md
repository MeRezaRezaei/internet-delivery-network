# IDN BACKLOG

## High Priority
- [ ] **IDN-040 Advanced Routing Engine**: Generate Xray routing rules based on real-time node metrics.
- [ ] **IDN-041 Multi-Node Batching**: Support provisioning a single tunnel across multiple hops (Chain) in one atomic transaction.
- [ ] **IDN-042 TLS/XHTTP Integration**: Add support for modern Xray transports (XHTTP, Split-HTTP) in the Dashboard.

## Done
- [x] **IDN-018 Control Plane Foundation**: Signal/Log dispatchers, Node registry, Xray Protobuf integration.
- [x] **IDN-019 Centralized MySQL Config DB**: Database schema, Models, Node inventory seeding.
- [x] **IDN-034 Centralized IDN Dashboard**: UI for node fleet monitoring, tunnel management, and real-time log tailing.
- [x] **IDN-020 CLI Orchestrator**: `idn` CLI shortcut and fleet orchestration logic.
- [x] **Control Plane Hardening**: Transactional batching, Redis Streams, Filesystem verification.

## Future Ideas
- [ ] **IDN-050 Automatic Failover**: Monitor node health and automatically re-route tunnels if a node goes down.
- [ ] **IDN-051 Traffic Visualization**: Add Grafana-like charts to the Dashboard.
- [ ] **IDN-052 Mobile Dashboard**: Responsive UI optimization for mobile management.
