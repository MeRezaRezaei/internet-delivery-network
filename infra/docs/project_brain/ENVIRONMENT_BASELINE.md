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

### Gateway (srv07) - Direct Access
Use direct Wireguard/Tailscale IP for the gateway:
```bash
ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no -i ~/.ssh/id_rsa_idn merezarezaei@10.255.1.7 "COMMAND"
```

### Insiders (srv01, srv04, etc.) - via Gateway srv07
Jump through the gateway to reach restricted internal nodes:
```bash
ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no -i ~/.ssh/id_rsa_idn -J merezarezaei@10.255.1.7 merezarezaei@10.255.1.4 "COMMAND"
```

### External Bridges (srv08, srv09, srv10) - Port 2022 via srv07
Jump through the gateway to reach external bridges on their customized SSH Port 2022:
```bash
ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no -i ~/.ssh/id_rsa_idn -o "ProxyCommand=ssh -i ~/.ssh/id_rsa_idn -W %h:%p merezarezaei@10.255.1.7" -p 2022 merezarezaei@10.255.1.9 "COMMAND"
```



