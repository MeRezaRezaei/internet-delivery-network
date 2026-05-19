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
1.  **Server 07 (Portal)**: 
    - Add `seed`: `a3f5c8d2e9b1f4a7c6d8e2f1b5a9c3d7`
    - Standardize tag to `portal` (or unique `portal-08`).
    - Remove `mode: auto` from `xhttpSettings`.
2.  **Server 08 (Bridge)**:
    - Change `address` to Origin IP `185.204.197.242` to bypass CDN.
    - Set `serverName` to `i-07.doctel.ir`.
    - Add matching `seed`.
    - Align `reverse` tag placement.
3.  **Validation**:
    - Check for `portal` registration in Server 07 logs.
    - Test SOCKS5 connectivity on Server 07 port 21080.

### Safety Measures
-   Always backup existing `.json` configs.
-   Use `systemctl reload` where possible.


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
