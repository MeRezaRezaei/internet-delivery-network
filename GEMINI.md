# Project Instructions

This project uses the **AI Brain** framework for contextual continuity and operational excellence. 

All AI agents MUST load the project brain before starting work.

## Initialization Workflow
Upon starting a new session, follow the instructions in:
[infra/docs/project_brain/ENTRYPOINT.md](./infra/docs/project_brain/ENTRYPOINT.md)

## Core Brain Files
- **Context:** [PROJECT_CONTEXT.md](./infra/docs/project_brain/PROJECT_CONTEXT.md)
- **Status:** [SESSION_STATE.md](./infra/docs/project_brain/SESSION_STATE.md)
- **Tasks:** [BACKLOG.md](./infra/docs/project_brain/BACKLOG.md)
- **Protocols:** [OPERATING_PROTOCOL.md](./infra/docs/project_brain/OPERATING_PROTOCOL.md)
- **Memory:** [infra/docs/project_brain/PRIVATE_MEMORY.md](./infra/docs/project_brain/PRIVATE_MEMORY.md) (Load this for credentials/secrets)

## Operational Mandate
Maintain synchronization between the codebase and the documentation in `infra/docs/project_brain/` at all times. Update the `SESSION_STATE.md` and `BACKLOG.md` at the end of every significant task.

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













hey this is new command ::




ROLE: Expert Laravel & Proxy Infrastructure Developer.

CONTEXT: The current `MarzbanSubscriptionController` is fundamentally flawed. It relies on outdated Marzban host generation logic. My infrastructure uses advanced Xray features (xhttp split-domain mapping for upload/download, pinned certificates for reverse proxies, and xmux). Marzban's database does NOT support these new Xray v1.8.8+ objects. 

OBJECTIVE: Rewrite the `MarzbanSubscriptionController` completely. 

STRICT RULES:
1. BAN MARZBAN HOSTS: You must COMPLETELY REMOVE `$this->getActiveInboundTags()` and the logic that queries `MarzbanHost`. Do not read from Marzban's host tables. 
2. UUID ONLY: The ONLY interaction with Marzban's database is to extract the user's `UUID` from the VLESS proxy settings.
3. SUBHOST PRIMACY: All generation MUST happen exclusively by looping over our custom Laravel model: `SubHost::where('is_active', true)->get()`.
4. THE EXTRA OBJECT: The core issue is the `extra` parameter in the VLESS URI. You must build the `$params['extra']` array in PHP so that when it is `json_encode`d, it matches the exact latest Xray standards.

XHTTP LOGIC REQUIREMENTS:
I have two main connection types that you must dynamically handle based on the SubHost type (Direct vs. Reverse):

A. REVERSE (Ports like 2096, 2083):
- The base URI requires `pcs` (PinnedPeerCertSha256).
- The `$extra` array MUST contain a `downloadSettings` object.
- Inside `downloadSettings`, it must define the download-specific `address`, `port`, `serverName` (SNI), and critically, a `certificates` array containing the raw PEM strings.

B. DIRECT (Port 8443):
- Usually behind Cloudflare. No `pcs` required in the base URI.
- The `$extra` array still uses `downloadSettings` to route download traffic through a different domain (e.g., `i-09.myavestar.ir`) while the main connection goes through the upload domain (e.g., `i-09.menudigi.ir`).

Both types MUST include the following in their `$extra` array:
- `headers` (User-Agent)
- `xPaddingBytes` (e.g., "100-500")
- `scMaxEachPostBytes`, `scMinPostsIntervalMs`
- `xmux` object (with maxConcurrency, cMaxReuseTimes, etc.)

ACTION:
Rewrite the `generateUris` and `buildVlessUri` functions to build this complex array structure dynamically based on the `$host` attributes from our custom database. Assume the `$host` model contains necessary fields (like `download_address`, `is_reverse`, `cert_pem`, `pcs`) or parse them from a JSON column.

OUTPUT MANDATE:
Output the FULL, complete PHP file from `<?php` to the end. No placeholders, no skipped functions, no comments like `// ... existing code ...`. I need a copy-paste ready file.