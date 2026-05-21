# Operating Protocol

## Server 07 (Portal) Non-Negotiable Mandates
- **Management Only**: Server 07 is the only "Pro" gateway out of the network. It MUST NOT be used for client-facing traffic or as a pass-through for anti-censorship services for general users.
- **Client Traffic Prohibition**: No client tunnels or proxies are allowed to terminate on Server 07's public IP.
- **Staff Access Only**: Existing tunnels on Server 07 are strictly for staff management and operational maintenance, governed by company regulations.
- **Risk Mitigation**: The stability of Server 07 is critical for project continuity. Any configuration that risks its public IP reputation or accessibility is forbidden.

## GFW DPI Investigation Findings (2026-05-21)
- **Hard Block Policy**: Direct incoming connections from US/EU IPs to Iranian server IPs (non-fronted) are blocked by default at the GFW/Provider level.
- **CDN Fronting Requirement**: Successful communication with Iranian nodes from outside requires valid CDN fronting (e.g., ArvanCloud) with matching SNI.
- **Domain Fronting Defense**: Cross-tenant domain fronting (spoofing SNI) is actively blocked by CDN edge security (403 Forbidden).

- **Fail Fast**: If a node is unresponsive, document the failure immediately and attempt the next logical step or an alternative path.

## Session start
1. Read startup files from `ENTRYPOINT.md`.
2. Reconfirm locked decisions and environment baseline.
3. Pick top unblocked backlog item.
4. Define acceptance criteria before coding.

## Role loop
1. Plan
2. Build
3. Test
4. Review
5. Document

## Mandatory SSH & Network Execution Rules
- **Rule of Timeout**: ALL remote commands MUST include a strict timeout (e.g., `-o ConnectTimeout=5` or `--max-time 10`) to prevent hanging the agent and wasting context.
- **SSH Jumps Rule (Hierarchy)**: srv07 is the ONLY gateway to insiders. Outsiders use direct Wireguard to srv07. To reach srv01, srv03, srv04, etc., jump through srv07. NO REVERSE JUMPS (e.g., jumping through 04 to reach 07).
- **Pass and Key SSH Rule**: Use identity file `~/.ssh/id_rsa_idn` for srv07. Use documented passwords (`asdfjkl`) for secondary hops if keys are missing.
- **Commit & Push**: After updating the AI Brain, ALWAYS commit and push the changes.



## Stop conditions
- Conflicting requirements affecting safety/business invariants.
- Need to break locked decisions without explicit approval.
- Cannot satisfy blocking tests/invariants.

## Session end
Must update state, backlog, changelog, and explicit next step.
