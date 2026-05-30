# IDN BACKLOG

## High Priority
- [x] **IDN-060 Centrifugo Real-time Streaming**: Replace polling loops with WebSocket streams.
- [x] **IDN-061 Native UUID Implementation**: Migrate users and subscriptions to native MySQL UUIDs.
- [x] **IDN-064 Asymmetric Split-Routing**: Decouple Download and Upload paths with spoofed xHTTP aggregation.
- [x] **IDN-062 Role-Based Node Topology**: Restrict node abilities to specific roles (Edge, Core, DNS).
- [x] **IDN-065 Local Xray Mock Fleet**: Build `idn:mock:fleet` for testing local native Xray binaries.
- [x] **IDN-063 Subscription Tiers API**: Build generic Xray subscription links with varied tier access levels.

## Done
- [x] **IDN-040 Advanced Routing Engine**: Generate Xray routing rules based on real-time node metrics.
- [x] **IDN-041 Multi-Node Batching**: Support provisioning a single tunnel across multiple hops (Chain) in one atomic transaction.
- [x] **IDN-042 TLS/XHTTP Integration**: Add support for modern Xray transports (XHTTP, Split-HTTP) in the Dashboard.
- [x] **IDN-018 Control Plane Foundation**: Signal/Log dispatchers, Node registry, Xray Protobuf integration.
- [x] **IDN-019 Centralized MySQL Config DB**: Database schema, Models, Node inventory seeding.
- [x] **IDN-034 Centralized IDN Dashboard**: UI for node fleet monitoring, tunnel management, and real-time log tailing.
# IDN BACKLOG

## High Priority
- [x] **IDN-060 Centrifugo Real-time Streaming**: Replace polling loops with WebSocket streams.
- [x] **IDN-061 Native UUID Implementation**: Migrate users and subscriptions to native MySQL UUIDs.
- [x] **IDN-064 Asymmetric Split-Routing**: Decouple Download and Upload paths with spoofed xHTTP aggregation.
- [x] **IDN-062 Role-Based Node Topology**: Restrict node abilities to specific roles (Edge, Core, DNS).
- [x] **IDN-065 Local Xray Mock Fleet**: Build `idn:mock:fleet` for testing local native Xray binaries.
- [x] **IDN-063 Subscription Tiers API**: Build generic Xray subscription links with varied tier access levels.

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

## Phase 3: Frontend Modernization (Vue + Vite)
- [x] **IDN-070 Vite & Vue Foundation**: Install `vue`, `@vitejs/plugin-vue`, set up `app.js` as the Vue mount point.
- [x] **IDN-071 Component Migration**: Convert the existing Dashboard `blade.php` files into Vue Single File Components (SFCs).
- [x] **IDN-072 WebSocket UI Integration**: Connect the new Vue Dashboard to the Centrifugo WebSocket stream for real-time traffic and logs.
- [x] **IDN-073 API Routing Transition**: Ensure the Laravel backend serves strictly as a JSON API, delegating view routing to `vue-router` (or Inertia.js).

## Phase 4: Xray Orchestration & Mocks
- [x] **IDN-080 Node Fleet Roles**: Implement differentiated node roles (DNS, Bridge, Edge) in the DB schema and seeding logic.
- [x] **IDN-081 Xray Local Mocks**: Download the Xray binary to the project and build artisan command `idn:mock:fleet` to spawn local mocked instances.
- [x] **IDN-082 Split-Routing Testing**: Write tests or commands to verify Asymmetric Split-Routing (xHTTP Aggregation) using the local fleet.
