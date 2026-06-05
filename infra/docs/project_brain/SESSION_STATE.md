# SESSION STATE: 2026-06-05

## Current Focus
- **Topic**: Modernizing Subscriptions (SplitHTTP/XHTTP)
- **Phase**: Fixing Infrastructure & Integration

## Achievements
- [x] Baremetal deployment at /opt/sub-service.
- [x] Marzban DB read-only integration.
- [x] Initial Host Manager UI (Vue.js).
- [x] Marzban token verification logic implemented.

## Active Constraints
- "h2 error" on assets in browser.
- UI shows white page despite 200 OK.
- Subscription logic needs to handle browser vs v2ray requests.
- Host manager needs to dynamically apply templates to Marzban hosts.

## Next Steps for Successor Agent
1. Fix UI asset protocol error (Nginx/HAProxy tuning).
2. Implement dual-mode subscription:
   - Browser -> HTML Page (Marzban-like but better).
   - V2Ray/Client -> Base64 URIs.
3. Link Host Manager to Marzban's hosts table to automatically generate configs for all active nodes.
