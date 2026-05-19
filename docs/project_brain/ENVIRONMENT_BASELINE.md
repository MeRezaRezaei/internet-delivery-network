# Environment Baseline

## Purpose
Define the authoritative runtime and test environment.

## Required
- Runtime authority (example: Docker Compose): Systemd services on distributed Linux VPS instances and Mikrotik RouterOS.
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
