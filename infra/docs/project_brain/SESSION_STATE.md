# SESSION STATE: 2026-06-05

## Current Focus
- **Topic**: Modernizing Subscriptions (SplitHTTP/XHTTP)
- **Phase**: Fixing Infrastructure & Integration

## Achievements
- [x] Baremetal deployment at /opt/sub-service.
- [x] Marzban DB integration (UUID extraction only).
- [x] **Automated XHTTP Configuration**: `extra` JSON is now generated from granular DB fields (Padding, XMUX, SC).
- [x] **CDN Support**: Added `is_cdn` logic to handle SSL/SNI dependencies correctly.
- [x] **Database Isolation**: Confirmed `marzban` (App) and `marzban-arvan` (Native) separation.
- [x] Host Manager UI (Blade) fully upgraded with advanced XHTTP controls.
- [x] Subscription "three hosts" bug resolved (refined host filtering and URI uniqueness).

## Active Constraints
- Reverse proxy certificates must be manually pasted into the Host Manager.
- Monitoring of GFW blocking on srv07 is mandatory.
- **Database Safety**: `marzban-arvan` is READ-ONLY. NEVER run app migrations against it.

## Next Steps for Successor Agent
1. Implement automated certificate fetching for Reverse SubHosts.
2. Add "Bulk Edit" features to the Host Manager for rapid configuration updates.
3. Enhance subscription page with specific downloaders for Sing-box and Shadowrocket.

## Next Steps for Successor Agent
1. Implement automated certificate fetching for Reverse SubHosts.
2. Add "Usage History" charts to the subscription page.
3. Implement bulk-actions in Host Manager (e.g., "Change Mode for all nodes").
