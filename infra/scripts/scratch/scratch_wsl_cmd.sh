#!/bin/bash
echo "=== TESTING SOCKS ON SRV04 (PORT 1081 & 1085) ==="
ssh -i ~/.ssh/id_rsa_no_p -o ConnectTimeout=5 -o StrictHostKeyChecking=no merezarezaei@185.204.197.242 "sshpass -p asdfjkl ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no root@10.255.1.4 '
echo \"SOCKS 1081:\"; curl -s -o /dev/null -w \"Connect: %{time_connect}s | Total: %{time_total}s | HTTP: %{http_code}\n\" --socks5-hostname MeRezaRezaei:Rez@9011438678@127.0.0.1:1081 https://www.google.com || echo \"Failed\"
echo \"SOCKS 1085:\"; curl -s -o /dev/null -w \"Connect: %{time_connect}s | Total: %{time_total}s | HTTP: %{http_code}\n\" --socks5-hostname MeRezaRezaei:Rez@9011438678@127.0.0.1:1085 https://www.google.com || echo \"Failed\"
'"
