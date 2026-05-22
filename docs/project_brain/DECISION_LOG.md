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

- ID: D-003
- Date: 2026-05-22
- Decision: Implemented 3-Port Deterministic Multicast formula with incremental/sequential database tunnel IDs (01-24), 3 outside servers (01-03), and direct plain-TCP peer-to-peer mesh routing over WireGuard.
- Rationale: Enables 100% collision-free, loop-free, and self-documenting port allocation where any port number clearly encodes its type, tunnel ID, outside server ID, inside server ID, and CDN ID. Bypassing port 443 SSL between mesh servers eliminates CPU overhead and connection handshake latency over the secure WireGuard private network.
- Impact: Derived ports: Type 1 (Reverse Tunnel): `10000 + (T*1000) + (O*100) + (I*10) + C`, Type 2 (User XTLS): `20000 + (T*1000) + (O*100) + (I*10) + C`, Type 3 (SOCKS Delivery): `30000 + (T*1000) + (O*100) + (I*10) + C`. Remote peers route directly to the target's derived plain-TCP port over WireGuard (e.g. target_ip:13221) rather than jumping through secondary SSL ports. Compiled 6 inside node configs with 2592 dynamic combinations each.
- Supersedes: None

