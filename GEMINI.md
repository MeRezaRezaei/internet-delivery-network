# Project Instructions

This project uses the **AI Brain** framework for contextual continuity and operational excellence. 

All AI agents MUST load the project brain before starting work.

## Initialization Workflow
Upon starting a new session, follow the instructions in:
[docs/project_brain/ENTRYPOINT.md](./docs/project_brain/ENTRYPOINT.md)

## Core Brain Files
- **Context:** [PROJECT_CONTEXT.md](./docs/project_brain/PROJECT_CONTEXT.md)
- **Status:** [SESSION_STATE.md](./docs/project_brain/SESSION_STATE.md)
- **Tasks:** [BACKLOG.md](./docs/project_brain/BACKLOG.md)
- **Protocols:** [OPERATING_PROTOCOL.md](./docs/project_brain/OPERATING_PROTOCOL.md)
- **Memory:** [docs/project_brain/PRIVATE_MEMORY.md](./docs/project_brain/PRIVATE_MEMORY.md) (Load this for credentials/secrets)

## Operational Mandate
Maintain synchronization between the codebase and the documentation in `docs/project_brain/` at all times. Update the `SESSION_STATE.md` and `BACKLOG.md` at the end of every significant task.

## AI Stability & Continuity Protocol
- **Threshold Warning:** The Gemini CLI environment may become unstable (stuck in "thinking" or crash) after high token/turn counts.
- **Session Handoff:** To prevent data loss, execute a **Manual Handoff** after completing any major step or if performance degrades.
- **Handoff Procedure:**
    1. Update all brain files (`SESSION_STATE.md`, `BACKLOG.md`, `CHANGELOG_AI.md`).
    2. Update `PRIVATE_MEMORY.md` with active credentials and local paths.
    3. State the exact next command or goal for the successor agent.
    4. Close the session gracefully.

## Mandatory Execution & Network Rules
- **Rule of Timeout**: ALL remote commands MUST include a timeout to prevent agent hangs. 
    - SSH: `-o ConnectTimeout=5`
    - Curl: `--max-time 10`
- **SSH Jumps Rule (Hierarchy)**: 
    - **Outsiders** (e.g., srv09, Agent): Have direct Wireguard/Tailscale access to the Gateway (**srv07**).
    - **Insiders** (e.g., srv01, srv03, srv04, srv06): MUST be reached by jumping through **Server 07**. 
    - **NO REVERSE JUMPS**: Do not jump through srv04 to reach srv07. srv07 is the entry point.
- **Pass and Key SSH Rule**: 
    - Use identity file `~/.ssh/id_rsa_idn` for direct access to **srv07**.
    - For jumps from srv07 to insiders, use the documented passwords (`asdfjkl`) if keys are not present.
    - Example (Direct to srv07): `ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no -i ~/.ssh/id_rsa_idn merezarezaei@10.255.1.7 "uptime"`
    - Example (Jump to srv04 via srv07): `ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no -i ~/.ssh/id_rsa_idn -J merezarezaei@10.255.1.7 merezarezaei@10.255.1.4 "uptime"`
- **Commit & Push**: After updating the AI Brain, ALWAYS commit and push the changes.


## Autonomous Connectivity
Agents MUST use `sshpass` and documented credentials from `PRIVATE_MEMORY.md` or `NETWORK_AND_ARCHITECTURE.md` to avoid interactive password prompts and ensure execution speed.

