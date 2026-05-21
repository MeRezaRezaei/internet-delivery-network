# Decision Log

## Entries
- ID: D-001
- Date: 2026-05-19
- Decision: Formalized Xray v26 Simplified Reverse Proxy over XHTTP as the project standard for IDN tunnels.
- Rationale: Verified that this pattern provides the best balance of stealth (CDN obfuscation), performance (XHTTP mode packet-up), and configuration simplicity (Simplified Reverse Proxy syntax).
- Impact: All future tunnels MUST match `email` and `seed` fields between Portal and Bridge. Bridges MUST use `mode: "packet-up"` for CDN compatibility.
- Supersedes: None

- ID: D-002
- Date: 2026-05-21
- Decision: Server 07 (Portal) is restricted to Management and Staff traffic only.
- Rationale: Server 07 is the primary "Pro" exit point for the network. Exposing it to client traffic or anti-censorship payloads carries an unacceptable risk of losing our only reliable path out.
- Impact: No client-facing tunnels or proxies shall be deployed on Server 07. All client traffic must be routed via other Iranian nodes (e.g., Server 01, 03, 04) which are in turn bridged to external nodes.
- Supersedes: None
