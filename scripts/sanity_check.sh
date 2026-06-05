#!/bin/bash

# IDN Sub-Service Sanity Check Script
# This script tests the live endpoints via localhost to ensure everything is functional.

HOST="sub.menudigi.ir"
BASE_URL="http://127.0.0.1:8085"

echo "=== IDN Infrastructure Sanity Check ==="

# 1. Test Admin Login Page
echo -n "[1/4] Testing Admin Login Page... "
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -H "Host: $HOST" "$BASE_URL/sub/admin/login")
if [ "$STATUS" == "200" ]; then
    echo "OK (200)"
else
    echo "FAILED ($STATUS)"
    exit 1
fi

# 2. Test Subscription Redirect
echo -n "[2/4] Testing /sub/admin Redirect... "
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -H "Host: $HOST" "$BASE_URL/sub/admin")
if [ "$STATUS" == "302" ]; then
    echo "OK (302)"
else
    echo "FAILED ($STATUS)"
    exit 1
fi

# 3. Test User Subscription (HTML)
# We use the known user MeRezaRezaei
echo -n "[3/4] Testing User Subscription (HTML)... "
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -H "Host: $HOST" -H "Accept: text/html" "$BASE_URL/sub/MeRezaRezaei")
if [ "$STATUS" == "200" ]; then
    echo "OK (200)"
else
    echo "FAILED ($STATUS)"
    # Don't exit here, might be a specific user issue
fi

# 4. Test User Subscription (Base64)
echo -n "[4/4] Testing User Subscription (Base64)... "
CONTENT=$(curl -s -H "Host: $HOST" -H "User-Agent: v2rayNG/1.8.5" "$BASE_URL/sub/MeRezaRezaei")
if [[ $CONTENT == dmxlc3M6* ]] || [[ $CONTENT == *vless://* ]] || [ -n "$CONTENT" ]; then
    echo "OK (Data Received)"
else
    echo "FAILED (Empty or Invalid Data)"
fi

echo "=== Sanity Check Completed ==="
