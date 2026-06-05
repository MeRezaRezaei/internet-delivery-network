# Operational Safety & Rollback Protocol

> [!CAUTION]
> # CRITICAL SECURITY MANDATE: SERVER 07 ABSOLUTE ZERO-TOUCH POLICY
> **Server 07 is the core, irreplaceable "Pro" management gateway. ANY configuration edit, systemctl reload, or ACTIVE testing/probing (including TLS handshakes, pings, traceroutes, or curls) targeting Server 07 will instantly expose it to GFW reputation blocking, risking permanent network lockout.**
> **DO NOT touch, modify, restart services, or execute any network/probing tests targeting these Server 07 identifiers from ANY server or environment:**
> *   **Public IP**: `185.204.197.242`
> *   **Private IP**: `10.255.1.7`
> *   **Domains**: Any domain starting with `i-07` (e.g. `i-07.menudigi.ir`, `i-07.doctel.ir`)
> **This mandate takes absolute precedence over all other tasks, scripts, and requests.**



## Overview
This project operates on high-load, production-critical servers with limited resources. Every modification MUST follow this safety protocol to ensure system stability and provide a clear path for immediate recovery.

---

## 1. Backup-Before-Action (BBA)
Before modifying any configuration file or service state:
1.  **Identify Targets**: List all files (e.g., `/etc/haproxy/haproxy.cfg`, `/etc/nginx/nginx.conf`) and services that will be affected.
2.  **Create Backups**: Use timestamped copies in a dedicated backup directory on the host or append `.bak.$(date +%Y%m%d_%H%M%S)` to the filename.
    - Example: `cp /etc/haproxy/haproxy.cfg /etc/haproxy/haproxy.cfg.bak.20260519_120000`
3.  **Verify Backup**: Ensure the backup file exists and is readable before proceeding.

---

## 2. The Change Loop
1.  **Plan**: Define the exact transformation and success criteria.
2.  **Act**: Apply the changes surgically.
3.  **Validate**: 
    - Syntax Check: Run `haproxy -c -f ...` or `nginx -t`.
    - Service Reload: Prefer `reload` over `restart` to minimize downtime.
    - Behavioral Check: Verify connectivity and SSL termination.

---

## 3. Rollback Procedure
If validation fails or unexpected behavior is observed:
1.  **Immediate Reversion**: Overwrite the modified file with the latest backup.
    - Example: `cp /etc/haproxy/haproxy.cfg.bak.20260519_120000 /etc/haproxy/haproxy.cfg`
2.  **Restore State**: Reload/Restart the service to return to the previous known-good state.
3.  **Root Cause Analysis**: Do not re-attempt the change until the failure is understood in a safe environment.

---

## 5. High-Risk / Network-Critical Operations
When modifying configurations that could sever the management connection (e.g., Xray configs on Gateway Server 07, Wireguard settings, or Mikrotik routing):

### A. Emergency Handoff (PROGRESS.md)
1.  **Initialize**: Create a `PROGRESS.md` file in the project root.
2.  **Document State**: Before every high-risk command, update `PROGRESS.md` with:
    - The exact command about to be run.
    - The expected outcome.
    - A step-by-step manual recovery guide (e.g., "Login via Public IP, run `cp backup config`, reload").
3.  **Purpose**: If the Gemini session is disconnected, the user can use the content of `PROGRESS.md` in a Gemini web session to guide manual recovery.

### B. Auto-Rollback (The 60-Second Test)
Network changes MUST be applied with a scheduled automatic reversion to prevent permanent lockout.
1.  **Backup**: Create a known-good backup.
2.  **Armed Rollback**: Start a background process that will revert the change after a timeout.
    - Example: `nohup bash -c "sleep 60 && cp /etc/xray/config.json.bak /etc/xray/config.json && systemctl reload xray" > /dev/null 2>&1 &`
3.  **Apply**: Apply the new configuration and reload the service.
4.  **Confirm**: Perform a connectivity check. If successful, **KILL the armed rollback process**.
5.  **Failure Scenario**: If the connection is lost, the background process will trigger after 60 seconds, restoring access automatically.

---

## 6. Unbreakable Direct Tunnels - Zero-Touch Policy
The three direct management tunnels represent the absolute core infrastructure of this network and MUST NOT be manipulated:
1.  **No Configuration Edits**: The configs in `/usr/local/etc/xray/` for `mmd-pg-us.json`, `mmd-pg.json`, and `mmd-pg-de.json` on srv07 and their respective bridge nodes are locked permanently.
2.  **No Service Restarts**: Systemd services `xray@mmd-pg-us`, `xray@mmd-pg`, and `xray@mmd-pg-de` must never be stopped, restarted, or reloaded.
3.  **No Connectivity Testing**: Never execute commands that direct heavy packet loads, port scans, or automated checks against their ports (`6443`, `7443`, `8443`, `9443`, `4443`, `5443`).
4.  **Static References**: Reference static backup configurations in `docs/project_brain/static_unbreakable_tunnels/` for validation.

