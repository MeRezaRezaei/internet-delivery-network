# SESSION STATE: 2026-06-06

## Current Focus
- **Topic**: Modernizing Subscriptions & Database Isolation
- **Phase**: Fixing Infrastructure & Integration

## Achievements
- [x] Baremetal deployment at `/opt/sub-service`.
- [x] **Database Isolation**: Successfully separated the sub-service database from the production Marzban database. The app database (`DB_HOST`) is now configured to Server 3's IP (`100.100.3.100`).
- [x] **Migrations Run**: Triggered and successfully completed all migrations programmatically on the isolated database on Server 3.
- [x] **Admin Dashboard Fixed**: Resolved the 500 internal server error on `/sub/admin/dashboard` caused by the missing tables, confirming that the login page now loads and routes perfectly.
- [x] **Automated XHTTP Configuration**: `extra` JSON is now generated from granular DB fields (Padding, XMUX, SC).
- [x] **CDN Support**: Added `is_cdn` logic to handle SSL/SNI dependencies correctly.
- [x] Host Manager UI (Blade) fully upgraded with advanced XHTTP controls.
- [x] Subscription "three hosts" bug resolved (refined host filtering and URI uniqueness).
- [x] **Real Protobuf Class Integration**: Resolved protobuf validation issues by loading real compiled classes in test bootstrap.
- [x] **Robust gRPC Mocking**: Fully mocked `Grpc\Call::startBatch` to handle inbound management and statistics collection.
- [x] **Forced Database Isolation in Tests**: Prevented config leaks by hardcoding connection overrides and forcing the `mysql` driver in test `setUp` (TestCase.php).

## Active Constraints
- **FALLBACK MODE ACTIVE**: All `/sub` traffic is currently routed to native Marzban (127.0.0.1:2020) via HAProxy.
- Reverse proxy certificates must be manually pasted into the Host Manager.
- Monitoring of GFW blocking on srv07 is mandatory.

## Next Steps for Successor Agent
1. **Verify Test Suite**: Once the terminal environment finishes executing or is reset, run `vendor/bin/phpunit` to confirm all 41 tests pass.
2. **Restore Sub-Service**: Once configurations are validated, revert HAProxy `host_sub` to `bk_sub_service`.
3. Implement automated certificate fetching for Reverse SubHosts.
