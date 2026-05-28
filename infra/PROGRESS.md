# EMERGENCY PROGRESS & RECOVERY LOG

## Current Status
- **Date**: 2026-05-19
- **CRITICAL RISK**: User is connected via one of the DE servers (08 or 10). **DO NOT** restart 'main' services (xray-tunnel, self-net, mmd-pg, etc.).
- **Overall Goal**: Implement advanced Xray obfuscation and Bridge expansion on Server 07 Gateway without losing connection.

---

## Current Step: Debugging Bridge 08 Tunnel
- **Status**: ACTIVE
- **Task**: Aligning 21-08-07-05 config with the working 23-01-07-05 pattern and bypassing ArvanCloud CDN.
- **Risk Level**: High (Potential to break connectivity if HAProxy is misconfigured)

### Plan
1.  **HAProxy Naming Refactor**:
    - Standardized backend names to `bk_srvXX_vless` and `bk_srvXX_xtls`.
    - Renamed Marzban backends to `bk_marzban_main` (2020) and `bk_marzban_pubg` (8002).
2.  **Bug Fixes**:
    - Aligned srv10 tunnel path to `/24-10-07-06`.
    - Added missing srv10 XTLS backend on port 5013.
    - Cleaned up `is_tunnel` ACL in `main_http`.
3.  **Validation**:
    - Ran `haproxy -c` syntax check (Passed).
    - Next: Apply and Reload.


### Safety Measures
-   Always backup existing `.json` configs.
-   Use `systemctl reload` where possible.


---

## [2026-05-28] - IDN Control Plane Hardening & Centralization
- **Hardened Signal Logic**: Replaced direct Redis Pub/Sub with Redis Streams + Consumer Groups for guaranteed signal delivery.
- **Transactional Integrity**: Implemented "Dry-Run All before Apply Any" batching to prevent partial configuration states.
- **Centralized DB**: Implemented MySQL-backed Node Inventory and Tunnel Registry (IDN-019).
- **Modern Dashboard**: Built visually rich Dashboard UI with real-time log tailing and node status (IDN-034).
- **CLI Orchestrator**: Developed `idn` CLI shortcut for fleet management (IDN-020).
- **Environment Stability**: Fixed Docker build issues by hardening the image with local gRPC/Protobuf/Redis extensions.
- **Test Suite**: 10/10 tests passing, covering hydration, dry-runs, and signal processing.

---

## Manual Recovery Instructions (If Disconnected)
... (Previous instructions remain valid)

1.  **Wait 2 Minutes**: The auto-rollback script is set for 90 seconds. It should restore the original config automatically.
2.  **Manual SSH**: If auto-rollback fails, try to jump through Server 04:
    - `ssh -i ~/.ssh/id_rsa_tailscale -o ProxyCommand="sshpass -p 'asdfjkl' ssh -W %h:%p root@10.255.1.4" root@10.255.1.7`
3.  **Manual Revert**: Once logged in, run:
    - `cp /root/backups/xray_config_backup.json /usr/local/etc/xray/config.json`
    - `systemctl reload xray@config`
4.  **Check Logs**: `journalctl -u xray@config -n 50`
