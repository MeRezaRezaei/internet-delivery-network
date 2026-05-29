#!/usr/bin/env bash
set -euo pipefail

TARGET=""
BRAIN_DIR="docs/project_brain"
UPDATE_AGENTS="false"
DRY_RUN="false"

while [[ $# -gt 0 ]]; do
  case "$1" in
    --target)
      TARGET="${2:-}"
      shift 2
      ;;
    --brain-dir)
      BRAIN_DIR="${2:-}"
      shift 2
      ;;
    --update-agents)
      UPDATE_AGENTS="true"
      shift
      ;;
    --dry-run)
      DRY_RUN="true"
      shift
      ;;
    *)
      echo "Unknown option: $1" >&2
      exit 1
      ;;
  esac
done

if [[ -z "$TARGET" ]]; then
  echo "Usage: $0 --target /path/to/project [--brain-dir docs/project_brain] [--update-agents] [--dry-run]" >&2
  exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
TEMPLATE_DIR="$ROOT_DIR/TEMPLATE"

if [[ ! -d "$TARGET" ]]; then
  echo "Target directory does not exist: $TARGET" >&2
  exit 1
fi

if [[ ! -d "$TARGET/$BRAIN_DIR" ]]; then
  echo "Target brain directory does not exist: $TARGET/$BRAIN_DIR" >&2
  echo "Run install_brain.sh first." >&2
  exit 1
fi

if [[ "$DRY_RUN" == "true" ]]; then
  echo "[dry-run] would sync: $TEMPLATE_DIR/docs/project_brain -> $TARGET/$BRAIN_DIR"
  if [[ "$UPDATE_AGENTS" == "true" ]]; then
    echo "[dry-run] would overwrite: $TARGET/AGENTS.md"
  fi
  exit 0
fi

cp -R "$TEMPLATE_DIR/docs/project_brain/." "$TARGET/$BRAIN_DIR/"

if [[ "$UPDATE_AGENTS" == "true" ]]; then
  cp "$TEMPLATE_DIR/AGENTS.md" "$TARGET/AGENTS.md"
fi

echo "Updated AI Brain in: $TARGET/$BRAIN_DIR"
if [[ "$UPDATE_AGENTS" == "true" ]]; then
  echo "Updated AGENTS.md"
else
  echo "AGENTS.md unchanged (use --update-agents to overwrite)."
fi
