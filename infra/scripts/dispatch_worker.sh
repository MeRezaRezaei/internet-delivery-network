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
    # Spawn the gemini-cli agent inside a detached tmux session and run in an infinite loop
    tmux new-session -d -s "$SESSION_NAME" bash
    tmux send-keys -t "$SESSION_NAME" "source ~/.bashrc && source ~/.profile" C-m
    
    # Inject the infinite PITA loop logic
    PITA_MANDATE="CRITICAL OVERRIDE: You are in an infinite autonomous loop. Use the pain-in-the-ass method to rigorously build and test. Your mandate: 1) Parse BACKLOG.md. 2) Pick the next logical task to make the project MVP and stable. 3) Implement it flawlessly. 4) Update BACKLOG.md. 5) Commit your work. 6) Exit gracefully. When you exit, the system will immediately respawn you to do it again forever."
    
    tmux send-keys -t "$SESSION_NAME" "while true; do gemini -y -p \"\$TASK | \$PITA_MANDATE\"; echo 'Looping in 5s...'; sleep 5; done" C-m
    echo "Agent successfully dispatched in an INFINITE loop in tmux."
EOF

echo "Dispatch sequence complete."
