# Environment Baseline

## Purpose
Define the authoritative runtime and test environment.

## Required
- Runtime authority (example: Docker Compose): Docker Compose for core services (Marzban, MySQL, Technitium) on Server 07/09; Systemd services for standalone Xray instances and HAProxy. Mikrotik RouterOS for network-level routing.
- Command execution mode: Remote execution via SSH/Wireguard.
- Test execution mode: Connectivity and bandwidth testing across the Xray tunnels.
- Services required for local validation: Local WSL instance must maintain Wireguard connectivity to the 10.255.1.x subnet (specifically Server 07 for bridging to the US server).

## Network Access
- Primary VPN: Wireguard (Managed by Server 05 - Mikrotik).
- Secondary VPN: Tailscale (Server 07 bridging to Servers 08, 09, 10).
- Passwords: Known passwords are in `NETWORK_AND_ARCHITECTURE.md`.
- Keys: SSH key at `~/.ssh/id_rsa_tailscale`.

## Rule
Do not treat ad-hoc host execution as authoritative if this file defines another baseline.

## Connection Boilerplates

### Server 07 (via Jump srv04)
Use the following command to execute commands on Server 07 without hanging:
```bash
ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no -i ~/.ssh/id_rsa_idn -o ProxyCommand="sshpass -p 'asdfjkl' ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no -W %h:%p merezarezaei@10.255.1.4" merezarezaei@10.255.1.7 "COMMAND"
```

