#!/bin/bash
echo "=== DUMPING SRV01 CONFIGS ==="
ssh -i ~/.ssh/id_rsa_no_p -o ConnectTimeout=5 -o StrictHostKeyChecking=no merezarezaei@185.204.197.242 "sshpass -p asdfjkl ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no root@10.255.1.1 '
echo \"=== c-01-01-01-01.json ===\"
cat /usr/local/etc/xray/c-01-01-01-01.json
echo \"=== c-01-01-01-05.json ===\"
cat /usr/local/etc/xray/c-01-01-01-05.json
echo \"=== RUNNING XRAY SERVICES ===\"
systemctl list-units | grep xray
'"

echo "=== DUMPING SRV03 CONFIGS ==="
ssh -i ~/.ssh/id_rsa_no_p -o ConnectTimeout=5 -o StrictHostKeyChecking=no merezarezaei@185.204.197.242 "sshpass -p asdfjkl ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no root@10.255.1.3 '
echo \"=== c-01-01-03-01.json ===\"
cat /usr/local/etc/xray/c-01-01-03-01.json
echo \"=== RUNNING XRAY SERVICES ===\"
systemctl list-units | grep xray
'"
