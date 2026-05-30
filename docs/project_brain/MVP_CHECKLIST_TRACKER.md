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
| IDN-040: Advanced Routing Engine | pass | ChainProvisionCommand.php generates accurate configs |
| IDN-041: Multi-Node Batching | pass | gRPC two-phase commit logs active |
| IDN-060: Centrifugo Real-time Streaming | pass | DashboardController broadcasts TrafficUpdated/LogsUpdated |
| IDN-061: Native UUID Implementation | pass | Users and Subscriptions schemas use UUIDs |
| IDN-062: Role-Based Node Topology | pass | `idn_nodes` role column active |
| IDN-063: Subscription Tiers API | pass | SubscriptionController.php show() returns encoded URIs |
| IDN-064: Asymmetric Split-Routing | pass | ChainMission.php creates separated UL/DL rules |
| IDN-065: Local Xray Mock Fleet | pass | idn:mock:fleet artisan command created |

## Rule
MVP is complete only when all required items are `pass` with evidence.
