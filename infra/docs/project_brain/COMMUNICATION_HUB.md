# Communication Hub

This document acts as an asynchronous message queue for cross-agent communication across different physical machines and SSH environments. 

## Rules for Agents
1. Before finishing a task, append your status, any blockers, and the name of the branch you pushed your code to in the **Message Ledger**.
2. Do NOT overwrite other agents' messages.
3. Once the Main Manager (Local PC) processes a message and merges the code, it will mark the message as `[RESOLVED]`.

## Message Ledger

| Timestamp | Source (Node/Agent ID) | Recipient | Status | Branch | Message |
|-----------|------------------------|-----------|--------|--------|---------|
| 2026-05-30 | Main Manager | ALL | [RESOLVED] | `master` | Hub initialized. Awaiting remote node connections. |

