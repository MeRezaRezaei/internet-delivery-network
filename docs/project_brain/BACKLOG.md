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


## WIP Rule
- Max doing items: 2
