# AI Brain

Reusable, project-agnostic brain framework for AI-driven software development.

## What it is
A lightweight file-based operating system for AI sessions:
- persistent memory,
- strict startup/shutdown workflow,
- backlog + risk + test gate discipline,
- checklist-based milestone completion.

## Install into any project
```bash
bash scripts/install_brain.sh --target /path/to/project
```

## Update an existing project from latest template
```bash
bash scripts/update_brain.sh --target /path/to/project
```

Optional (also refresh `AGENTS.md`):
```bash
bash scripts/update_brain.sh --target /path/to/project --update-agents
```

## Minimal way to talk to AI
You only need this:

```text
Use the project brain and continue: <what I want>
```

The AI should read the brain docs itself (starting from `docs/project_brain/ENTRYPOINT.md`).

## Included
- `TEMPLATE/AGENTS.md`
- `TEMPLATE/docs/project_brain/*`
- `scripts/install_brain.sh`
- `scripts/update_brain.sh`
- setup and usage docs
