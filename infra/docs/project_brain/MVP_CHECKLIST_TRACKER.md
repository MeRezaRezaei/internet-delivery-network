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
| Strategy schema canonicalized and validated | pass | `XrayStrategy` and `XrayDomainStrategy` enums implemented and used in `XrayConfigRenderer` |
| Event/channel contracts fixed and versioned | pass | `LogsUpdated` and `TrafficUpdated` events fixed and standardized |
| Determinism rules locked and tested | pass | `XrayConfigContractTest::test_xray_config_is_deterministic` verified |
| Contract test suite exists | pass | `tests/Feature/Contract/` suite covers Events, Xray Config, and Signals |
| MVP out-of-scope boundaries enforced | todo | - |
| Risk guard minimum rules implemented | pass | `RiskGuard::validateConfig` and `NodeObserver` block unsafe bindings and destructive node deletions. Tested in `RiskGuardTest.php`. |
| Error recovery idempotency tested | todo | - |
| Performance benchmark recorded | todo | - |
| Gap-recovery behavior defined and tested | todo | - |

## Rule
MVP is complete only when all required items are `pass` with evidence.
