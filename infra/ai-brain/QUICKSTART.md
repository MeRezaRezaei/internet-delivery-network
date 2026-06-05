# Quickstart

## 1) Install
```bash
bash scripts/install_brain.sh --target /path/to/your/project
```

## 2) Fill base context once
Edit:
- `docs/project_brain/PROJECT_CONTEXT.md`
- `docs/project_brain/ENVIRONMENT_BASELINE.md`

## 3) Start working with AI
Say only:

```text
Use the project brain and continue: <your request>
```

Example:
```text
Use the project brain and continue: implement login API.
```

You do not need to manually tell the AI which files to read; brain startup flow covers that.

## 4) Keep existing projects updated
```bash
bash scripts/update_brain.sh --target /path/to/your/project
```

If you want AGENTS template refresh too:
```bash
bash scripts/update_brain.sh --target /path/to/your/project --update-agents
```
