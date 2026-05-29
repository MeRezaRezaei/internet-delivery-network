#!/usr/bin/env bash
set -euo pipefail

TARGET=""
BRAIN_DIR="docs/project_brain"
FORCE="false"

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
    --force)
      FORCE="true"
      shift
      ;;
    *)
      echo "Unknown option: $1" >&2
      exit 1
      ;;
  esac
done

if [[ -z "$TARGET" ]]; then
  echo "Usage: $0 --target /path/to/project [--brain-dir docs/project_brain] [--force]" >&2
  exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
TEMPLATE_DIR="$ROOT_DIR/TEMPLATE"

if [[ ! -d "$TARGET" ]]; then
  echo "Target directory does not exist: $TARGET" >&2
  exit 1
fi

mkdir -p "$TARGET/$BRAIN_DIR"

if [[ -e "$TARGET/AGENTS.md" && "$FORCE" != "true" ]]; then
  echo "AGENTS.md already exists in target. Use --force to overwrite." >&2
  exit 1
fi

cp "$TEMPLATE_DIR/AGENTS.md" "$TARGET/AGENTS.md"

# Copy brain docs tree
cp -R "$TEMPLATE_DIR/docs/project_brain/." "$TARGET/$BRAIN_DIR/"

echo "Installed AI Brain template into: $TARGET"
echo "- AGENTS.md"
echo "- $BRAIN_DIR/*"
echo "Next: edit $BRAIN_DIR/PROJECT_CONTEXT.md and start AI with ENTRYPOINT.md flow."
