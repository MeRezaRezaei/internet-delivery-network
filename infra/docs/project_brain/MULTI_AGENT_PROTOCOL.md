# Multi-Agent Development Environment (MADE) Protocol

The IDN project operates using a heavily parallelized AI development environment. This protocol defines how the **Project Manager (Primary AI Orchestrator)** and **Subagents (AI Workers)** interact, synchronize state, and avoid context overlap.

## 1. Agent Taxonomy and Roles

### The Project Manager (Primary Orchestrator)
- **Role:** Leads the overarching architectural decisions, monitors subagents, and maintains the AI Brain.
- **Quota Rule:** The Project Manager MUST NOT consume its own execution quota writing code or running tests. Its quota is strictly reserved for managing subagents, merging their work, and documenting progress.
- **Responsibilities:**
  - Update SESSION_STATE.md with active agent fleet statuses.
  - Distribute tasks from BACKLOG.md to Subagents.
  - Merge subagent workspaces back into the primary codebase.

### Subagents (AI Workers)
- **Role:** Specialized, ephemeral agents invoked to execute single, distinct features or bug fixes.
- **Responsibilities:**
  - Operate strictly within their assigned Workspace branch.
  - Execute their provided prompt with extreme focus.
  - Do not update high-level tracking files like SESSION_STATE.md or BACKLOG.md unless explicitly instructed to.
  - Report back a detailed summary to the Project Manager upon completion.

## 2. Workspace Isolation and Branching
- **Strict Isolation:** Two subagents must NEVER modify the same file concurrently in the main branch. 
- **Worktrees:** Subagents must be invoked in isolated branches (e.g., via the orchestration tool's workspace: branch mode) to avoid database migration collisions, composer lock conflicts, and uncommitted code overriding.

## 3. Communication and Handoff State
- Subagents communicate exclusively via completion messages sent to the Project Manager.
- The Project Manager tracks estimated completion times. If a subagent silently hangs or loops due to tool errors (e.g., misconfigured WSL environment), the Project Manager must kill the subagent and spawn a native internal replacement.

## 4. The Multi-Agent Mandate
If there are multiple non-dependent tasks in the BACKLOG.md, the Project Manager MUST invoke parallel subagents rather than executing sequentially. This is to maximize development velocity.

## 5. Distributed Tmux Multi-Machine Architecture
The IDN AI ecosystem supports massive horizontal scaling across physical VPS nodes. The Main Project Manager (Local PC) orchestrates Remote AI Workers securely over SSH.

### Core Mechanisms
- **Persistence via Tmux:** All remote agents MUST be spawned inside a detached `tmux` session. If the Local PC goes offline, remote agents continue their tasks until completion.
- **Git-Backed Asynchronous Hub:** Remote nodes cannot communicate via real-time system messages. All cross-agent communication happens via Git.
- **Scheduler Agent:** A specialized local subagent, known as the Scheduler, loops in the background using `/schedule`. It SSHs into remote nodes, reads the `tmux capture-pane`, and writes summaries back to the Main Project Manager via `send_message`.

### Spawning a Remote Agent
To fire up an agent on a remote server, the Main Manager issues the following sequence:
1. `ssh user@remote_ip "cd /path/to/project && git pull"`
2. `ssh user@remote_ip "tmux new-session -d -s worker_1 'gemini-cli --task \"Execute BACKLOG item X. Push to branch feat/worker-1 and update COMMUNICATION_HUB.md\"'"`

### Cross-Agent Synchronization (The Hub)
Remote agents push their progress to `infra/docs/project_brain/COMMUNICATION_HUB.md` along with their isolated code branches. The Main Manager then pulls this code, evaluates it, and merges it back into `master`.
