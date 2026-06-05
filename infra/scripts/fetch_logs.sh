#!/bin/bash

echo "=== HAProxy on srv01 ==="
ssh -i ~/.ssh/id_rsa_no_p -o ConnectTimeout=5 -o StrictHostKeyChecking=no merezarezaei@185.204.197.242 "sshpass -p asdfjkl ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no root@10.255.1.1 '
cat /etc/haproxy/haproxy.cfg
'"

echo "=== HAProxy on srv03 ==="
ssh -i ~/.ssh/id_rsa_no_p -o ConnectTimeout=5 -o StrictHostKeyChecking=no merezarezaei@185.204.197.242 "sshpass -p asdfjkl ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no root@10.255.1.3 '
cat /etc/haproxy/haproxy.cfg
'"

echo "=== srv04 Xray logs (last 20 lines) ==="
ssh -i ~/.ssh/id_rsa_no_p -o ConnectTimeout=5 -o StrictHostKeyChecking=no merezarezaei@185.204.197.242 "sshpass -p asdfjkl ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no root@10.255.1.4 '
echo \"=== xray@16-01-04-01 logs ===\"
journalctl -u xray@16-01-04-01 -n 20 --no-pager
echo \"=== xray@20-01-04-05 logs ===\"
journalctl -u xray@20-01-04-05 -n 20 --no-pager
'"

echo "=== srv09 Xray logs (last 20 lines) ==="
ssh -i ~/.ssh/id_rsa_no_p -o ConnectTimeout=5 -o StrictHostKeyChecking=no merezarezaei@185.204.197.242 "ssh -i ~/.ssh/id_rsa_idn -o ConnectTimeout=5 -o StrictHostKeyChecking=no -p 2022 root@10.255.1.9 '
echo \"=== xray@16-01-04-01 logs ===\"
journalctl -u xray@16-01-04-01 -n 20 --no-pager || tail -n 20 /var/log/syslog | grep xray
echo \"=== xray@20-01-04-05 logs ===\"
journalctl -u xray@20-01-04-05 -n 20 --no-pager
'"
