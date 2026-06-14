#!/bin/bash

# IDN Tunnel Health Check Script (v2.0)
# Target: Server 07 (Portal)

echo "=== IDN Infrastructure Health Check $(date) ==="
echo "Testing connectivity via local SOCKS/Mixed ports..."

TEST_URL="https://www.google.com/generate_204"
TIMEOUT=5

# Port Mapping (Port:Description)
declare -A PORTS=(
    ["21081"]="German Bridge (Portal SOCKS - 08-05)"
    ["24081"]="US Bridge (Portal SOCKS - 09-01)"
    ["5011"]="Marzban Inbound (VLESS/XHTTP 21-08-07-05)"
    ["5012"]="Marzban Inbound (VLESS/XHTTP 24-01-07-06)"
)

# Test Proxy Connectivity
for port in "${!PORTS[@]}"; do
    description=${PORTS[$port]}
    printf "[%-45s] Port %-5s: " "$description" "$port"
    
    # Perform CURL test (using localhost as these are bound to 127.0.0.1 or 0.0.0.0)
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --socks5-hostname 127.0.0.1:$port --connect-timeout $TIMEOUT $TEST_URL)
    
    if [ "$HTTP_CODE" == "204" ]; then
        echo -e "\e[32mPASS\e[0m"
    else
        echo -e "\e[31mFAIL (Code: $HTTP_CODE)\e[0m"
    fi
done

echo "=========================================="
echo "Service Status (Native & Docker):"

# Check Technitium (Port 5380)
if curl -s -I http://127.0.0.1:5380 | grep -q "200 OK"; then
    echo -e "Technitium DNS UI (5380): \e[32mRUNNING\e[0m"
else
    echo -e "Technitium DNS UI (5380): \e[31mDOWN\e[0m"
fi

# Check Marzban API (Port 2020)
if curl -s -I http://127.0.0.1:2020 | grep -q "404 Not Found" || curl -s -I http://127.0.0.1:2020 | grep -q "200 OK"; then
    echo -e "Marzban API (2020): \e[32mRUNNING\e[0m"
else
    echo -e "Marzban API (2020): \e[31mDOWN\e[0m"
fi

echo "=========================================="
echo "Detailed Systemd Service Status:"
systemctl list-units "dns.service" "mysql.service" "docker.service" --no-pager
