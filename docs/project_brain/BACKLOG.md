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
| IDN-013 | P0 | Diagnose Domestic US Proxy Bottlenecks | done | IDN-012 | Root causes and connectivity parameters mapped |
| IDN-014 | P1 | Remediate srv09 Xray Conflicts & Align SSH | done | IDN-013 | Monolithic service stopped, template active; Mesh SSH verified |
| IDN-015 | P0 | Establish Stable SOCKS Outbounds on Domestic Nodes | done | IDN-014 | Verified active SOCKS connections on srv01, srv03, srv04 |
| IDN-016 | P1 | Reorganize and Clean Up Repository Root | done | - | Files sorted into keys/, configs/, scripts/ and tracked |
| IDN-017 | P1 | Analyze Misunderstandings & Upgrade Brain with Prompts | done | IDN-016 | Prompts created in PROMPT_LIBRARY.md to block critical pitfalls |
| IDN-018 | P1 | Centralized Config Database Design | done | IDN-017 | Detailed architectural DB proposal created |
| IDN-019 | P1 | Implement Centralized MySQL Config DB | todo | IDN-018 | Tables created and populated on srv07 MySQL |
| IDN-020 | P1 | Develop CLI Orchestrator (idn-ctl) | todo | IDN-019 | Python script compiles, validates, and deploys configs |
| IDN-021 | P1 | Multicast Config Generator Alignment | done | IDN-017 | Realigned HAProxy config generator to user's 6-node matrix and compiled all configs |
| IDN-022 | P0 | Unified Xray Config Compilation | done | IDN-021 | Compiled unified, replicated 2592 scenario Xray JSON config bypassing SOCKS5 |


## WIP Rule
- Max doing items: 2
