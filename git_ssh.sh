#!/bin/bash
sshpass -p 'asdfjkl' ssh -o StrictHostKeyChecking=no -J merezarezaei@10.255.1.7 "$@"
