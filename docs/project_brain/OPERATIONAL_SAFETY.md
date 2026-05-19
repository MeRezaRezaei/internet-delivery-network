# Operational Safety & Rollback Protocol

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

## 4. Documentation
Append every significant operational change and its outcome (including rollbacks) to `docs/project_brain/CHANGELOG_AI.md`.
