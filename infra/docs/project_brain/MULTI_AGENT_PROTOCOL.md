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
