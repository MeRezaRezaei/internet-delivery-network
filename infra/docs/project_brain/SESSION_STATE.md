# SESSION STATE: 2026-06-14

## Current Focus
- **Topic**: Marzban Sub-Service Integration & Local Docker Migration
- **Phase**: Implementation & Environmental Stabilization

## Achievements
- [x] **Remote Work Capture (srv03)**: Successfully SSH'd into 100.100.3.100, identified unpushed work in `/opt/sub-service`, consolidated it into `feat/marzban-sub-things` branch, and cleaned the remote workspace.
- [x] **Local Workspace Consolidation**: Pulled `feat/marzban-sub-things` locally and merged `master` (using `--allow-unrelated-histories`) to restore Docker infrastructure and 5NF schema while preserving Marzban features.
- [x] **HAProxy Gateway Integration**: Successfully deployed HAProxy as a centralized gateway on the Tailscale IP `100.100.4.100`. Configured safe, non-conflicting ports (8081, 10095-10099, 3318-3319, 6391) to front the Laravel app and development tools while maintaining strict isolation from the host's live services.
- [x] **Environment Stabilization**: Built `internet-delivery-network-app` with `grpc`, fixed Redis conflicts, hardened DB migrations, and enforced HAProxy-only entrance.
- [x] **IDN-053 Fix CI/CD Docker and Migration Instability**: Fixed CPU instruction set errors in docker-compose, resolved conflicting migrations (`physical_ports` and `idn_nodes`), fixed composer lock file permission issue, and bundled `xray` binary directly in Laravel Dockerfile. All 23 tests passing.
- [x] **IDN-050 Automatic Failover Daemon**: Dockerized `idn:node:monitor` to continuously poll node health and automate tunnel routing.
- [x] **IDN-042 TLS/XHTTP Integration**: Created Split-HTTP models, migrations, and hydrated them into `XrayConfigRenderer`.
- [x] **IDN-036 Dockerization gRPC bottlenecks**: Added PHP CLI worker pools and native DNS resolver to remove `artisan serve` bottleneck.
- [x] **Knowledge Graph Repaired**: Surgically fixed corruption in `memory.jsonl` to restore Knowledge Graph functionality.

## Done
- **Remote Work Migration (2026-06-14):**
    - Consolidated unpushed Marzban sub-service features from srv03 into `feat/marzban-sub-things`.
    - Unified branch with `master` to restore containerized infrastructure.
- **HAProxy Gateway Deployment (2026-06-14):**
    - Integrated HAProxy into Docker Compose to front all dev services on Tailscale IP.
    - Verified end-to-end routing to Laravel Admin Panel via `100.100.4.100:8081`.

## Active Constraints
- Host PHP (8.3) is incompatible with Laravel 13; must use Docker (PHP 8.5).
- Docker socket requires `sg docker` for access.
- Port conflicts with existing host containers resolved via HAProxy fronting on safe ports.
- **Mandate**: All development tools and the app MUST be accessed via the HAProxy gateway on `100.100.4.100`.

## Next Steps for Successor Agent
1. **Marzban Verification**: Verify Admin Panel login and rewritten subscription generation via the new gateway.
2. **Database Schema Repair**: Resolve the `errno: 150` foreign key constraint issue in the isolated Marzban mock migration.

## Handover Metadata
- **Gateway IP**: 100.100.4.100 (Tailscale)
- **App URL**: http://100.100.4.100:8081/sub/admin/login
- **Xray APIs**: 10095, 10097, 10099
- **Databases**: 3318 (IDN), 3319 (Marzban)
- **Redis**: 6391
- **Tests**: Core environment verified reachable.
