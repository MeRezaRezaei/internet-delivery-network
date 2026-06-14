#!/bin/bash
echo "=== TESTING ACTIVE SOCKS ON IRAN DOMESTIC SERVERS ==="

echo "----------------------------------------------------"
echo "1. TESTING SERVER 01 (Shahriar)"
ssh -i ~/.ssh/id_rsa_no_p -o ConnectTimeout=5 -o StrictHostKeyChecking=no merezarezaei@185.204.197.242 "sshpass -p asdfjkl ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no root@10.255.1.1 '
echo \"[Port 1081 (Marzban - c-01-01-01-01)]:\"
curl -s -I --max-time 10 --socks5-hostname MeRezaRezaei:Rez@9011438678@127.0.0.1:1081 https://www.google.com | head -n 1 || echo \"FAILED\"
echo \"[Port 1085 (Arvan - c-01-01-01-05)]:\"
curl -s -I --max-time 10 --socks5-hostname MeRezaRezaei:Rez@9011438678@127.0.0.1:1085 https://www.google.com | head -n 1 || echo \"FAILED\"
'"

echo "----------------------------------------------------"
echo "2. TESTING SERVER 03 (Bamdad)"
ssh -i ~/.ssh/id_rsa_no_p -o ConnectTimeout=5 -o StrictHostKeyChecking=no merezarezaei@185.204.197.242 "sshpass -p asdfjkl ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no root@10.255.1.3 '
echo \"[Port 1081 (Marzban - c-01-01-03-01)]:\"
curl -s -I --max-time 10 --socks5-hostname MeRezaRezaei:Rez@9011438678@127.0.0.1:1081 https://www.google.com | head -n 1 || echo \"FAILED\"
'"

echo "----------------------------------------------------"
echo "3. TESTING SERVER 04 (Shiraz)"
ssh -i ~/.ssh/id_rsa_no_p -o ConnectTimeout=5 -o StrictHostKeyChecking=no merezarezaei@185.204.197.242 "sshpass -p asdfjkl ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no root@10.255.1.4 '
echo \"[Port 1081 (Marzban - 16-01-04-01)]:\"
curl -s -I --max-time 10 --socks5-hostname MeRezaRezaei:Rez@9011438678@127.0.0.1:1081 https://www.google.com | head -n 1 || echo \"FAILED\"
echo \"[Port 1085 (Arvan - 20-01-04-05)]:\"
curl -s -I --max-time 10 --socks5-hostname MeRezaRezaei:Rez@9011438678@127.0.0.1:1085 https://www.google.com | head -n 1 || echo \"FAILED\"
'"
