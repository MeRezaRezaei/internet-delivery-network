#!/bin/bash
# check_worker_status.sh
# Usage: ./check_worker_status.sh <ssh_target> <tmux_session_name>

if [ "$#" -ne 2 ]; then
    echo "Usage: $0 <ssh_target> <tmux_session_name>"
    exit 1
fi

SSH_TARGET=$1
SESSION_NAME=$2

echo "Checking status of $SESSION_NAME on $SSH_TARGET..."

ssh $SSH_TARGET << EOF
    if tmux has-session -t "$SESSION_NAME" 2>/dev/null; then
        echo "Status: RUNNING"
        echo "--- Latest Output ---"
        tmux capture-pane -p -t "$SESSION_NAME" | tail -n 20
    else
        echo "Status: STOPPED / FINISHED"
    fi
EOF
