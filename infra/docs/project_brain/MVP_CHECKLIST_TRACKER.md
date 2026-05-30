# MVP Checklist Tracker

## Purpose
Track MVP acceptance requirements and prevent premature "MVP complete" claims.

## Source checklist
- `docs/project_brain/mvp/MVP_CHECKLIST_TEMPLATE.md`

## Status legend
- pass
- partial
- todo

## Tracker table
| Item | Status | Evidence |
|---|---|---|
| Runtime states canonical and mapped end-to-end | partial | `idn:fleet:reconcile` command implemented to sync DB/Redis |
| Strategy schema canonicalized and validated | todo | - |
| Event/channel contracts fixed and versioned | todo | - |
| Determinism rules locked and tested | todo | - |
| Contract test suite exists | todo | - |
| MVP out-of-scope boundaries enforced | todo | - |
| Risk guard minimum rules implemented | todo | - |
| Error recovery idempotency tested | todo | - |
| Performance benchmark recorded | todo | - |
| Gap-recovery behavior defined and tested | todo | - |

## Rule
MVP is complete only when all required items are `pass` with evidence.
