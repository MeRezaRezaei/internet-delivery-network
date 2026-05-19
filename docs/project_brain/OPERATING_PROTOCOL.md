# Operating Protocol

## Command Timeout Rule
- **Mandatory Timeouts**: Every shell command MUST have an explicit timeout (e.g., `timeout 10s ...` or `ssh -o ConnectTimeout=5 ...`).
- **Maximum Wait**: Never wait more than 30 seconds for any network-dependent command.
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

## Stop conditions
- Conflicting requirements affecting safety/business invariants.
- Need to break locked decisions without explicit approval.
- Cannot satisfy blocking tests/invariants.

## Session end
Must update state, backlog, changelog, and explicit next step.
