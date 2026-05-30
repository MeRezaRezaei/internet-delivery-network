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

sshpass -p 'asdfjkl' ssh -o StrictHostKeyChecking=no -J merezarezaei@10.255.1.7 $SSH_TARGET << EOF
    # Spawn the gemini-cli agent inside a detached tmux session
    tmux new-session -d -s "$SESSION_NAME" bash
    tmux send-keys -t "$SESSION_NAME" "source ~/.bashrc && source ~/.profile" C-m
    tmux send-keys -t "$SESSION_NAME" "gemini -y -p '$TASK'" C-m
    echo "Agent successfully dispatched in tmux."
EOF

echo "Dispatch sequence complete."
