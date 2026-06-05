# Master Merge & Integration Guide (Session: 2026-05-28)

## 1. Overview for Merging Agent
This branch (`feat/control-plane-hardened`) contains the core foundation of the IDN Control Plane. It has been battle-hardened with:
- **MySQL Inventory**: `idn_nodes` and `idn_tunnels` tables.
- **Redis Stream Signals**: Guaranteed delivery for node configurations.
- **Transactional Batching**: Zero-downtime, dry-run-validated deployments.
- **Hardened Hydration**: Multi-protocol support (VLESS/VMess/SOCKS).

## 2. Integration with Upcoming Branch (Tailscale/Technitium/Wireshark)
When merging the new API branch, follow these "Glue" principles:

### A. Tailscale Integration (The Mesh)
- **Status Mapping**: Map Tailscale's `Status.Peer[].Online` boolean to the `idn_nodes.is_active` field in MySQL.
- **Dynamic Listen**: Use Tailscale IPs (100.x.x.x) for all Xray inbounds. Update `XrayProtobufHydrator` to prioritize Tailscale interface IPs.
- **Failover**: If Tailscale reports a node as `Offline`, trigger the `ControlPlaneManager` to migrate tunnels to the next available peer.

### B. Technitium Integration (The DNS)
- **Policy Control**: Add a "DNS Policy" section to the IDN Dashboard.
- **API Flow**: UI Button (e.g. "Block Ads") -> Call Technitium API (`/api/settings/setBlocklist`) -> Update Xray `DNS` config on all nodes via a broadcast signal (`targetNode=all`).

### C. Wireshark/Tshark Integration (The Visibility)
- **Live Capture**: Add a "Traffic Analysis" tab to the Tunnel view in the Dashboard.
- **Execution**: Dashboard -> `idn:log:tail` (already implemented) + a new wrapper for `tshark` API to stream packet summaries to the UI.

## 3. Post-Merge Validation Steps
1. Run `php artisan migrate` to ensure MySQL schemas are unified.
2. Run `php artisan test` - All 11 hydration/control tests MUST pass.
3. Verify that the `.env` has all new API keys:
   - `TAILSCALE_API_KEY`
   - `TECHNITIUM_API_KEY`
   - `WIRESHARK_API_ENDPOINT`

## 4. Architectural Vision (The "Glue")
Do NOT build new logic. **Orchestrate existing tools.**
- If Tailscale knows a node is down, don't ping it yourself—trust Tailscale.
- If Technitium handles the blocklist, don't write Xray routing rules—trust Technitium.
- The IDN Control Plane is the **UX layer** and the **Signal Dispatcher** for these professional tools.
