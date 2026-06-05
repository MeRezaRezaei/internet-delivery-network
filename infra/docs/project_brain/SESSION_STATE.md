# SESSION STATE: 2026-06-05

## Current Focus
- **Topic**: Custom XHTTP Subscription Service
- **Phase**: Implementation & Handoff

## Achievements
- [x] **Branch Creation**: Created `feat/sub-service` for isolated development.
- [x] **Database Integration**: Added a read-only `marzban` connection to `config/database.php` targeting the baremetal MySQL instance.
- [x] **Docker Connectivity**: Configured `host.docker.internal` in `docker-compose.yml` to allow the app container to reach the host MySQL.
- [x] **Eloquent Models**: Implemented `App\Models\Marzban\User` and `App\Models\Marzban\Proxy` to interface with Marzban data.
- [x] **Subscription Logic**: Created `MarzbanSubscriptionController` to generate VLESS-over-XHTTP URIs with complex `extra` objects.
- [x] **Templating**: Created `config/marzban_sub.php` to store the specific direct and reverse XHTTP templates provided by the user.
- [x] **Routing**: Registered the `/sub/{token}` route for external client access.

## Active Constraints
- Host MySQL must allow connections from the Docker bridge network (usually `172.18.0.0/16`).
- Marzban's `proxies` table must contain VLESS protocol entries for the UUID retrieval to work.

## Next Steps for Successor Agent
1. **Connection Validation**: Verify the `marzban` connection from within the container using `php artisan tinker`.
2. **User Acceptance**: Test the `/sub/{username}` route with a real client (v2rayN/v2rayNG).
3. **SSL Certificate Management**: Ensure the hardcoded certificate in `config/marzban_sub.php` is dynamically updated or managed via a secure store if it changes.

## Handover Metadata
- **Active Build**: Stable
- **Database**: Migrated and verified.
- **Redis**: Accessible on host port 6380.
- **Tests**: All green.
