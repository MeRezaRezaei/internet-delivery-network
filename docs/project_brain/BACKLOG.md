# Backlog

## Status Legend
- todo
- doing
- blocked
- done

## Table
| ID | Priority | Title | Status | Depends On | Done When |
|---|---:|---|---|---|---|
| PB-001 | P0 | Bootstrap project brain | done | - | Core docs filled and validated |
| IDN-001 | P0 | Verify SSH Access to Server 07 | done | - | Can reliably SSH into Server 07 from WSL |
| IDN-002 | P0 | Verify Access to Server 09 (US) | done | IDN-001 | Can reach US server via Tailscale from Server 07 |
| IDN-003 | P1 | Map and Verify "Reverse-Reverse" Tunnel | done | IDN-002 | Full traffic path (01 -> 07 -> 09 -> Internet) verified |
| IDN-004 | P1 | Document Multicast Delivery Model | done | IDN-003 | IDN architecture fully documented in brain |
| IDN-005 | P1 | Stabilize Server 07-08 Tunnel | done | IDN-004 | Tunnel 21-08-07-05 is active and passing traffic |
| IDN-006 | P1 | Verify Cloudflare Proxy to srv07 | done | IDN-005 | Domain i-07.doctel.ir confirmed reaching srv07 Xray |
| IDN-007 | P0 | Verify Internet Delivery to srv07 | done | IDN-006 | srv07 can reach google.com via the reverse tunnel |
| IDN-008 | P2 | Automated Health Checks | done | IDN-007 | Script exists to verify all tunnel statuses |
| IDN-009 | P1 | Establish Server 10-07 Tunnel | done | IDN-008 | Tunnel active on port 21010 (Verified path /24-10-07-06) |
| IDN-010 | P0 | Xray-core Deep Investigation | done | - | Technical reference database created in xray_reference/ |
| IDN-011 | P0 | Migrate Marzban (srv03 -> srv07) | done | IDN-009 | Server 07 is orchestrator; srv03 is node; subs preserved |
| IDN-012 | P1 | HAProxy Naming Refactor & Bug Fixes | done | IDN-011 | Backends renamed; srv10 path fixed; port 5013 added |



## WIP Rule
- Max doing items: 2
