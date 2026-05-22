# Prompt Library & Guardrails

## Startup Prompt
Read `AGENTS.md` and execute the `docs/project_brain/ENTRYPOINT.md` startup flow.
Before taking any action, run the **Correction Prevention Guardrails** below to ensure perfect alignment with historical constraints.
Then, continue the highest-priority unblocked backlog item in `BACKLOG.md`.

---

## Review Prompt
Review all proposed changes against `TEST_GATE.md`, `RISK_REGISTER.md`, and the `DECISION_LOG.md`.
Ensure that no zero-touch systems are affected, and verify configuration syntax patterns before service reloads.
List any blocking issues or violations first.

---

## Correction Prevention Guardrails

To prevent critical errors, session disruptions, or infrastructure lockouts, every subsequent agent MUST evaluate their planned execution against the following four historical pitfalls before changing any file or executing systemctl commands:

### Guardrail 1: Zero-Touch Management Tunnels Protection
*   **Context**: The user is connected to the network *via* one of the German servers (srv08 or srv10) through srv07. 
*   **Trigger**: Any planned configuration edit, systemctl reload, or service restart targeting the active management tunnels.
*   **Pitfall**: Restarting or interrupting `xray@mmd-pg-us`, `xray@mmd-pg`, or `xray@mmd-pg-de` (or their associated configs in `/usr/local/etc/xray/`) will instantly disconnect the user and the agent, causing a permanent operational lockout.
*   **Prompt Instruction**:
    ```text
    CRITICAL: Stop and verify that the planned task does NOT modify or reload 'mmd-pg', 'mmd-pg-us', or 'mmd-pg-de' configurations or systemd services. If yes, ABORT immediately. These tunnels are 100% Zero-Touch.
    ```

### Guardrail 2: Xray v26+ Simplified Reverse Proxy Syntax Alignment
*   **Context**: The network leverages Xray-core v26+ Simplified Reverse Proxy logic.
*   **Trigger**: Developing or updating Portal or Bridge Xray `.json` configurations.
*   **Pitfall**: 
    1. Legacy outbound formats (using the nested `vnext` syntax) will fail to register on the Bridge side in v26.
    2. Path or name mismatches (e.g., dial paths with mismatched prefixes like `/c-01-01-03-01` vs `/11-01-03-01`) will prevent the reverse tunnel from establishing.
*   **Prompt Instruction**:
    ```text
    TECHNICAL CHECK: Ensure that:
    1. The Bridge outbound config uses the modern Simplified Reverse Proxy syntax (no 'vnext' block, direct 'address' and 'port' in outbound settings).
    2. The 'email' and 'seed' fields match perfectly between Portal and Bridge.
    3. The tunnel path (e.g., '/24-10-07-06/xtls') and SOCKS port settings align exactly between srv07's HAProxy backend routing rules, srv07's Xray inbound, and the Bridge node's outbound config.
    4. There are no redundant/mismatched prefixes (e.g. 'c-') left in names or paths.
    ```

### Guardrail 3: Marzban Multi-Panel Subdomain Isolation
*   **Context**: Server 07 runs multiple Marzban panels (the PUBG panel with 400+ users on port 8002 and the legacy panel on port 2020).
*   **Trigger**: Modifying routing rules in HAProxy to isolate or proxy Marzban panels.
*   **Pitfall**: Path-based isolation (e.g., routing via `/m7/` or referrers) will fail and break the panel due to internal root-relative application assets and API routes causing collisions.
*   **Prompt Instruction**:
    ```text
    ARCHITECTURE STANDARD: Do NOT use URL path-based routing or referrer headers to isolate multiple Marzban panels on the same gateway. You MUST use Host subdomain-based routing (e.g., 'dash.new-state.ir' routing to port 8002 and 'panel.new-state.ir' to port 2020) with clean HAProxy Host-header matches.
    ```

### Guardrail 4: Mesh Network SSH Port 2022 Match
*   **Context**: The internal WireGuard/Mesh/Tailscale networks map server-to-server connections.
*   **Trigger**: Initiating direct SSH commands or jump connections to bridge nodes (srv08, srv09, srv10) over internal IPs (like `10.255.1.9`).
*   **Pitfall**: SSH on these interfaces is configured to listen on **Port 2022**, NOT standard port 22. Attempting connection on port 22 will result in `Connection refused`.
*   **Prompt Instruction**:
    ```text
    CONNECTION CHECK: When executing SSH jumps or direct shell commands to external bridge nodes (srv08, srv09, srv10) using private mesh IPs (10.255.1.x), you MUST explicitly specify port 2022 (e.g., '-p 2022').
    ```
