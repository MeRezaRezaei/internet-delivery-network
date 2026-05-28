# Architectural Guardrails: The "Economical Engineering" Framework

This document defines how we judge code quality and prioritize automation based on "Pain Mitigation" and "Economic Logic."

## 1. The Core Principle: Pain-Driven Development
We do not build for "perfection" or "industry standards" alone. We build to solve the **Pain in the Ass (PITA)**.

- **Automation Logic**: If a task is performed frequently and manual execution is error-prone or time-consuming (e.g., node config updates), automate it immediately. If a task is performed once a year, manual execution is the most economical path.
- **Testing Logic**: We do not aim for 100% coverage. We write tests for "Red State" risks—parts of the system where a failure leads to permanent lockout, GFW detection, or massive data loss.
- **Code Level**: We aim for "Enough." Code must be readable, structured, and solve the immediate problem without over-engineering for hypothetical future needs.

## 2. Decision Logic for AI Agents
When an AI agent is tasked with a decision, it must ask:
1. Does this solve a major PITA?
2. Does the failure of this logic lead to a "Red State" (network death)?
3. Is the complexity of the solution proportional to the frequency of the problem?

## 3. Critical Weaknesses & "Silly Mistake" Prevention
The following patterns are identified as **MODERATE** risk weaknesses (Hardened on 2026-05-28):

| Weakness | Status | Mitigation Implemented |
| :--- | :--- | :--- |
| **Partial Sync State** | ✅ SOLVED | Transactional Batch processing with dry-run-all-before-apply-any logic. |
| **Redis Signal Overflow** | ✅ SOLVED | Migrated to Redis Streams with Consumer Groups for guaranteed delivery. |
| **Certificate Path Blindness** | ✅ SOLVED | Recursive filesystem check during hydration prevents "Dead on Arrival" configs. |
| **Silent OS Port Clashes** | ✅ SOLVED | "Verify-After-Apply" logic ensures Xray core actually listening on requested port. |
| **Registry Blindness** | ✅ SOLVED | Node Registry with Heartbeats (60s TTL) provides real-time fleet visibility. |
| **Log Silos** | ✅ SOLVED | Real-time Log Streaming engine via Redis Streams. |

### Remaining Future Risks (PITA Status):
1. **Hydration Regression**: If Xray v27+ fundamentally changes Protobuf field types. 
2. **Redis Memory Pressure**: Extreme log volume could fill Redis if XTRIM isn't aggressive enough.
3. **Database Consistency**: Laravel DB state vs Redis Node state during manual node reboots.


## 4. The "Zero-Touch" Guardrail
Never forget: **Server 07 is the Pro Management Gateway.** Any "Hot Reload" logic targeting Srv07 must have 2x the validation rigor.
