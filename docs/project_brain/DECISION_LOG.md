# Decision Log

## Entries
- ID: D-001
- Date: 2026-05-19
- Decision: Formalized Xray v26 Simplified Reverse Proxy over XHTTP as the project standard for IDN tunnels.
- Rationale: Verified that this pattern provides the best balance of stealth (CDN obfuscation), performance (XHTTP mode packet-up), and configuration simplicity (Simplified Reverse Proxy syntax).
- Impact: All future tunnels MUST match `email` and `seed` fields between Portal and Bridge. Bridges MUST use `mode: "packet-up"` for CDN compatibility.
- Supersedes: None
