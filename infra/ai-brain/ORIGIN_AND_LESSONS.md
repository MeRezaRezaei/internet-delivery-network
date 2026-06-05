# Origin And Lessons

This template came from a real long-running project where many AI sessions had to continue work safely.

## What worked
- A strict startup file (`ENTRYPOINT.md`) reduced context drift.
- A single ground-truth state file (`SESSION_STATE.md`) kept continuity.
- A priority backlog prevented random task switching.
- Explicit risk + test gate docs prevented unsafe shortcuts.
- Mandatory end-of-session updates kept memory fresh.

## What failed before adding this structure
- AI repeated already done tasks.
- Inconsistent decisions appeared across sessions.
- Tests were run in the wrong environment.
- Work ended with stale docs and unclear next steps.

## Hard rules to keep
1. AI must always read startup files first.
2. AI must update brain docs in the same session as code changes.
3. MVP/release claims must be tied to checklist gates.
4. Operational/runtime baseline (Docker, cloud, etc.) must be explicit.

## Typical anti-patterns
- Treating `CHANGELOG_AI.md` as optional.
- Letting `BACKLOG.md` and `SESSION_STATE.md` diverge.
- Using host-local assumptions instead of project runtime baseline.
- Claiming done without acceptance evidence.
