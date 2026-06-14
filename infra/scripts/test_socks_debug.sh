#!/bin/bash
echo "=== DEBUGGING SOCKS ON IRAN DOMESTIC SERVERS (FIXED CURL SYNTAX) ==="

echo "----------------------------------------------------"
echo "1. DEBUGGING SERVER 01 (Shahriar)"
ssh -i ~/.ssh/id_rsa_no_p -o ConnectTimeout=5 -o StrictHostKeyChecking=no merezarezaei@185.204.197.242 "sshpass -p asdfjkl ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no root@10.255.1.1 '
echo \"[Port 1081 (01-01-01-01)]:\"
curl -v -I --max-time 5 --socks5-hostname 127.0.0.1:1081 --proxy-user \"MeRezaRezaei:Rez@9011438678\" https://www.google.com 2>&1 | grep -E \"Connecting to|SOCKS|HTTP/\"
echo \"[Port 1085 (01-01-01-05)]:\"
curl -v -I --max-time 5 --socks5-hostname 127.0.0.1:1085 --proxy-user \"MeRezaRezaei:Rez@9011438678\" https://www.google.com 2>&1 | grep -E \"Connecting to|SOCKS|HTTP/\"
'"

echo "----------------------------------------------------"
echo "2. DEBUGGING SERVER 03 (Bamdad)"
ssh -i ~/.ssh/id_rsa_no_p -o ConnectTimeout=5 -o StrictHostKeyChecking=no merezarezaei@185.204.197.242 "sshpass -p asdfjkl ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no root@10.255.1.3 '
echo \"[Port 1081 (c-01-01-03-01)]:\"
curl -v -I --max-time 5 --socks5-hostname 127.0.0.1:1081 --proxy-user \"MeRezaRezaei:Rez@9011438678\" https://www.google.com 2>&1 | grep -E \"Connecting to|SOCKS|HTTP/\"
'"

echo "----------------------------------------------------"
echo "3. DEBUGGING SERVER 04 (Shiraz)"
ssh -i ~/.ssh/id_rsa_no_p -o ConnectTimeout=5 -o StrictHostKeyChecking=no merezarezaei@185.204.197.242 "sshpass -p asdfjkl ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no root@10.255.1.4 '
echo \"[Port 1081 (16-01-04-01)]:\"
curl -v -I --max-time 5 --socks5-hostname 127.0.0.1:1081 --proxy-user \"MeRezaRezaei:Rez@9011438678\" https://www.google.com 2>&1 | grep -E \"Connecting to|SOCKS|HTTP/\"
echo \"[Port 1085 (20-01-04-05)]:\"
curl -v -I --max-time 5 --socks5-hostname 127.0.0.1:1085 --proxy-user \"MeRezaRezaei:Rez@9011438678\" https://www.google.com 2>&1 | grep -E \"Connecting to|SOCKS|HTTP/\"
'"
