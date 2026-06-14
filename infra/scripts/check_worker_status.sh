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

if ! sshpass -p 'asdfjkl' ssh -o StrictHostKeyChecking=no -J merezarezaei@10.255.1.7 $SSH_TARGET "tmux has-session -t $SESSION_NAME 2>/dev/null"; then
    echo "Status: STOPPED / FINISHED"
    exit 0
fi

echo "Status: RUNNING"
echo "--- Latest Output ---"
sshpass -p 'asdfjkl' ssh -o StrictHostKeyChecking=no -J merezarezaei@10.255.1.7 $SSH_TARGET "tmux capture-pane -pt $SESSION_NAME | tail -n 20"
