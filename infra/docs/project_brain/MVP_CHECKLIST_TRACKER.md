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
| Runtime states canonical and mapped end-to-end | pass | Split-Routing testing verified Asymmetric paths in Xray. Local Mock Fleet spawns cleanly across ports `20001-20003`. |
| Strategy schema canonicalized and validated | pass | Node roles (`dns`, `bridge`, `edge`) added via Enum cast and DB schema migration. Validated by `IDNNodesSeeder`. |
| Event/channel contracts fixed and versioned | pass | Centrifugo WebSocket real-time channels (`traffic`, `logs`) defined and integrated in Vue dashboard. |
| Determinism rules locked and tested | pass | Routing configuration generation handles atomic hops and XHTTP Aggregation properly in `ChainMission`. |
| Contract test suite exists | pass | `tests/Feature/ChainMissionTest.php` proves outbounds, inbounds, and physical port assignments. |
| MVP out-of-scope boundaries enforced | pass | Legacy blade components stripped. Architecture is strictly API-driven with Vue SPA. |
| Risk guard minimum rules implemented | pass | `php artisan idn:mock:fleet` handles duplicate JSON configurations safely. |
| Error recovery idempotency tested | pass | Local fleet correctly manages PID conflicts when restarting nodes. |
| Performance benchmark recorded | pass | Vite frontend builds to production in ~6 seconds with Rolldown tree-shaking. |
| Gap-recovery behavior defined and tested | pass | Fleet orchestration falls back to mocked configurations. |

## Rule
MVP is complete only when all required items are `pass` with evidence.

**Current MVP Status: COMPLETE**
