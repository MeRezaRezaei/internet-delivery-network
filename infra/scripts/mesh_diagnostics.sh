#!/bin/bash

echo "### STARTING IDN MESH DIAGNOSTICS ###"
echo "Date: $(date)"
echo ""

nodes=("10.255.1.1:srv01" "10.255.1.3:srv03" "10.255.1.4:srv04")

for entry in "${nodes[@]}"; do
    ip=${entry%%:*}
    name=${entry#*:}
    echo "=================================================="
    echo "NODE: $name ($ip)"
    echo "=================================================="
    
    # SSH into the node and run diagnostics
    ssh -i ~/.ssh/id_rsa -o StrictHostKeyChecking=no -o ConnectTimeout=5 root@$ip << 'INNER_EOF'
        echo "[1] Checking Listening SOCKS Ports:"
        for port in 1081 1082 1085 1086; do
            if ss -tulpn | grep -q ":$port "; then
                echo "  - Port $port is LISTENING"
                
                # Test SOCKS proxy connectivity
                res=$(curl -s -o /dev/null -w "HTTP:%{http_code} TIME:%{time_total}s" -x socks5h://MeRezaRezaei:Rez%409011438678@127.0.0.1:$port --max-time 10 https://www.google.com/generate_204 2>&1)
                echo "    * Test (google.com): $res"
            else
                # Port is not listening
                :
            fi
        done
        
        echo ""
        echo "[2] Latency and Ping Tests (3 packets each):"
        for target in "10.255.1.7:srv07" "10.255.1.9:srv09-mesh" "100.100.5.100:srv09-ts" "8.8.8.8:google-dns"; do
            tip=${target%%:*}
            tname=${target#*:}
            printf "  - Ping to %-12s (%-13s): " "$tname" "$tip"
            ping_res=$(ping -c 3 -W 2 $tip 2>&1)
            if [ $? -eq 0 ]; then
                avg=$(echo "$ping_res" | tail -n 1 | awk -F '/' '{print $5}')
                loss=$(echo "$ping_res" | grep -oP '\d+(?=% packet loss)')
                echo "SUCCESS (avg: ${avg}ms, loss: ${loss}%)"
            else
                echo "FAILED"
            fi
        done
        
        echo ""
        echo "[3] DNS Resolution of CDN Tunnels (Local node side):"
        for domain in "i-01.doctel.ir" "i-04.doctel.ir" "i-03.docreverse.ir" "i-04.docreverse.ir"; do
            printf "  - Resolving %-20s: " "$domain"
            ip_res=$(getent hosts $domain | awk '{print $1}' | head -n 1)
            if [ -n "$ip_res" ]; then
                echo "$ip_res"
            else
                # Try nslookup or dig as fallback
                ip_res=$(nslookup $domain 2>/dev/null | grep -A1 "Name:" | grep "Address:" | awk '{print $2}')
                if [ -n "$ip_res" ]; then
                    echo "$ip_res (via nslookup)"
                else
                    echo "FAILED TO RESOLVE"
                fi
            fi
        done
INNER_EOF
    echo ""
done

echo "### DIAGNOSTICS COMPLETED ###"
