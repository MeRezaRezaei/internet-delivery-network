# Session State

## Last Updated
- Date: 2026-05-19
- Owner: Gemini CLI

## Current Stage
- Stage: Infrastructure Evaluation Complete
- Focus: Consolidating IDN architectural patterns and prepare for maintenance/automation.

## Done
- Mapped full 10-server topology and verified access paths (WSL -> 04 -> 07 -> 09/01).
- Deciphered the "Reverse-Reverse Proxy" trick (Iran as Portal, US as Bridge).
- Documented the "Multicast IDN" delivery model where the US Origin pushes traffic to Iranian Edge nodes.
- Recovered critical technical tips from a "broken" session log, specifically regarding Xray v26 Simplified Reverse Proxy.
- Standardized the architectural patterns in `NETWORK_AND_ARCHITECTURE.md` to align with Xray v26 (XHTTP, seed/email matching).
- **Incident Recovery (2nd Time):** Documented the second occurrence of a broken session. Re-synced state from the AI Brain to maintain continuity. Verified that no critical architectural data was lost due to the robust "AI Brain" documentation requirement.
- **Functional stabilization of the DE-08 tunnel:** Fixed email mismatch, standardized reverse tags, and bypassed CDN to achieve stable 21-08-07-05 tunnel registration. Verified traffic flow via SOCKS5 test.
- **Cloudflare Proxy Verification:** Confirmed that `i-07.doctel.ir` (Cloudflare/ArvanCloud) correctly routes traffic to Server 07 HAProxy and then to Xray backends. Verified active sessions on tunnel `23-01-07-05`.
- **Topology Documentation:** Created `docs/TOPOLOGY.md` mapping the node relationships and traffic flow.
- **Credential Autonomy:** Successfully established SSH access to Server 07 using documented credentials (`merezarezaei`/`asdfjkl`).

## Not Done
- Automated health checks for the various VLESS tunnels.
- Centralized management of Xray configs (currently scattered across nodes).

## Immediate Next Objective
- **Verify Internet via Cloudflare Tunnel:** Perform a SOCKS5 test on Server 07 to confirm that traffic routed through the Cloudflare/ArvanCloud reverse tunnel (`23-01-07-05`) successfully reaches the global internet via Server 09.
- Implement automated health checks for all active tunnels.

## Known Constraints
- Access to most nodes requires jumping through Server 04.
- Xray version v26+ syntax is mandatory for the modern Simplified Reverse Proxy logic.
- ArvanCloud CDN requires `packet-up` mode for reliable XHTTP streaming.
