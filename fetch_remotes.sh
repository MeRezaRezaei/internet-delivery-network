#!/bin/bash
cd /mnt/c/Users/MeRezaRezaei/Documents/projects/internet-delivery-network
chmod +x git_ssh.sh
git config core.sshCommand "./git_ssh.sh"
git remote add de-worker merezarezaei@100.100.3.100:/home/merezarezaei/internet-delivery-network || true
git fetch de-worker
git remote add us-worker merezarezaei@100.100.5.100:/home/merezarezaei/internet-delivery-network || true
git fetch us-worker
