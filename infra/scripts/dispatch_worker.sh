#!/bin/bash
# dispatch_worker.sh
# Usage: ./dispatch_worker.sh <ssh_target> <tmux_session_name> <gemini_task>
# Example: ./dispatch_worker.sh root@10.0.0.5 worker-node-1 "Implement XYZ"

if [ "$#" -ne 3 ]; then
    echo "Usage: $0 <ssh_target> <tmux_session_name> <gemini_task>"
    exit 1
fi

SSH_TARGET=$1
SESSION_NAME=$2
TASK=$3

echo "Dispatching task to $SSH_TARGET in tmux session $SESSION_NAME..."

ssh $SSH_TARGET << EOF
    # Ensure project is up to date
    cd /root/projects/internet-delivery-network || exit 1
    git fetch origin
    git checkout master
    git pull origin master
    
    # Spawn the gemini-cli agent inside a detached tmux session
    tmux new-session -d -s "$SESSION_NAME" "gemini-cli --task '$TASK'"
    echo "Agent successfully dispatched in tmux."
EOF

echo "Dispatch sequence complete."
