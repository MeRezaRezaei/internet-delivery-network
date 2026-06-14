# Risk Register

| ID | Risk | Severity | Probability | Trigger | Detection | Mitigation | Owner | Status |
|---|---|---|---|---|---|---|---|---|
| R-000 | **CATASTROPHIC: Bare-Metal Interaction** | **CRITICAL** | low | AI tool execution on host services/apps | Port conflicts, filesystem errors, or live app downtime | **MANDATORY DOCKER ISOLATION**: All execution must be confined to Docker containers. Check environment with `env` before sensitive commands. No host port binding without HAProxy gateway check. | AI | **ACTIVE** |
| R-001 | Template risk | medium | medium | - | - | - | AI | open |
| R-002 | Session Continuity | high | high | Platform timeout/disconnect | Disconnected session or "brain fog" in new agent | Maintain strict synchronization with AI Brain docs (docs/project_brain/) | AI | open |
