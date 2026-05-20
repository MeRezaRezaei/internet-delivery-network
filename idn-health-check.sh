#!/bin/bash

# IDN Tunnel Health Check Script
# Target: Server 07 (Portal)

echo "=== IDN Tunnel Health Check $(date) ==="
echo "Testing connectivity via local SOCKS/Mixed ports..."

TEST_URL="https://www.google.com/generate_204"
TIMEOUT=5

# Port Mapping (Port:Description)
declare -A PORTS=(
    ["1000"]="Local (Direct)"
    ["1080"]="German Bridge (Portal Tag - 08-06)"
    ["1100"]="Multi-Bridge (Portal Tag - 10-05)"
    ["1010"]="US Forward Proxy (to srv09:10808)"
    ["1011"]="DE Forward Proxy (to srv08:10808)"
    ["1012"]="DE PG Forward Proxy"
    ["21080"]="German Bridge (Arvan - 08-05)"
    ["21081"]="German Bridge (Cloudflare - 08-06)"
)

# Individual Tunnel Tag Verification (for US Tunnel 23-01-07-05)
# Since 23-01-07-05 uses the 'portal' tag, we test port 1100 as its proxy.

for port in "${!PORTS[@]}"; do
    description=${PORTS[$port]}
    printf "[%-40s] Port %-5s: " "$description" "$port"
    
    # Perform CURL test
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --socks5-hostname 10.255.1.7:$port --connect-timeout $TIMEOUT $TEST_URL)
    
    if [ "$HTTP_CODE" == "204" ]; then
        echo -e "\e[32mPASS\e[0m"
    else
        echo -e "\e[31mFAIL (Code: $HTTP_CODE)\e[0m"
    fi
done

echo "=========================================="
echo "Detailed Xray Service Status:"
systemctl list-units "xray@*" --state=active --no-pager
