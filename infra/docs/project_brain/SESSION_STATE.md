# SESSION STATE: 2026-06-05

## Current Focus
- **Topic**: Modernizing Subscriptions (SplitHTTP/XHTTP)
- **Phase**: Fixing Infrastructure & Integration

## Achievements
- [x] Baremetal deployment at /opt/sub-service.
- [x] Marzban DB integration (UUID extraction only).
- [x] Full rewrite of `MarzbanSubscriptionController` following Xray v1.8.8+ standards.
- [x] Implementation of advanced XHTTP logic (Direct vs Reverse) with Split-Domain mapping.
- [x] Host Manager UI (Vue.js) upgraded for advanced XHTTP fields.
- [x] **New Blade-based Admin Panel** implemented with Marzban `.env` authentication.
- [x] UI Protocol error fixed via HAProxy H2 tuning.
- [x] Beautiful "Marzban-like but better" subscription page with QR codes and deep links.

## Active Constraints
- Reverse proxy certificates must be manually pasted into the Host Manager (automated fetch from srv07 later?).
- Monitoring of GFW blocking on srv07 is mandatory.
- Admin Panel relies on `/opt/Marzban/.env` for SUDO credentials.

## Next Steps for Successor Agent
1. Implement automated certificate fetching for Reverse SubHosts.
2. Add "Usage History" charts to the subscription page.
3. Implement bulk-actions in Host Manager (e.g., "Change Mode for all nodes").
