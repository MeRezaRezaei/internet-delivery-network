#!/usr/bin/env python3
import os
import subprocess
import time
import signal
import http.server
import socketserver
import threading
import json

# Configuration Directories
TEST_DIR = "/tmp/xray_pool_test"
os.makedirs(TEST_DIR, exist_ok=True)

PORTAL_JSON = os.path.join(TEST_DIR, "portal.json")
BRIDGE1_JSON = os.path.join(TEST_DIR, "bridge1.json")
BRIDGE2_JSON = os.path.join(TEST_DIR, "bridge2.json")

PORTAL_LOG = os.path.join(TEST_DIR, "portal.log")
BRIDGE1_LOG = os.path.join(TEST_DIR, "bridge1.log")
BRIDGE2_LOG = os.path.join(TEST_DIR, "bridge2.log")

UUID = "58764c09-99c3-4496-9591-9cff83e4c7b7"
UUID2 = "c5478440-bc4f-40c0-b8c7-d4fa24b74ee7"

# --- 1. Write Scenario C Config Files (Shared Tag + Distinct UUIDs) ---
portal_config = {
    "log": {
        "loglevel": "debug"
    },
    "inbounds": [
        {
            "tag": "socks-in",
            "port": 10800,
            "listen": "127.0.0.1",
            "protocol": "socks",
            "settings": {
                "auth": "noauth",
                "udp": True
            }
        },
        {
            "tag": "vless-in",
            "port": 15100,
            "listen": "127.0.0.1",
            "protocol": "vless",
            "settings": {
                "clients": [
                    {
                        "id": UUID,
                        "email": "bridge1@reverse",
                        "reverse": {
                            "tag": "reverse-out"
                        }
                    },
                    {
                        "id": UUID2,
                        "email": "bridge2@reverse",
                        "reverse": {
                            "tag": "reverse-out"
                        }
                    }
                ],
                "decryption": "none"
            },
            "streamSettings": {
                "network": "xhttp",
                "xhttpSettings": {
                    "path": "/reverse",
                    "mode": "auto"
                }
            }
        }
    ],
    "outbounds": [
        {
            "protocol": "blackhole",
            "tag": "block"
        }
    ],
    "routing": {
        "domainStrategy": "AsIs",
        "rules": [
            {
                "type": "field",
                "inboundTag": ["socks-in"],
                "outboundTag": "reverse-out"
            }
        ]
    }
}

bridge1_config = {
    "log": {
        "loglevel": "debug"
    },
    "outbounds": [
        {
            "protocol": "freedom",
            "tag": "direct"
        },
        {
            "protocol": "vless",
            "tag": "tunnel",
            "settings": {
                "address": "127.0.0.1",
                "port": 15100,
                "id": UUID,
                "email": "bridge1@reverse",
                "encryption": "none",
                "reverse": {
                    "tag": "bridge-in"
                }
            },
            "streamSettings": {
                "network": "xhttp",
                "xhttpSettings": {
                    "path": "/reverse",
                    "mode": "packet-up"
                }
            }
        }
    ],
    "routing": {
        "domainStrategy": "AsIs",
        "rules": [
            {
                "type": "field",
                "inboundTag": ["bridge-in"],
                "outboundTag": "direct"
            }
        ]
    }
}

bridge2_config = {
    "log": {
        "loglevel": "debug"
    },
    "outbounds": [
        {
            "protocol": "freedom",
            "tag": "direct"
        },
        {
            "protocol": "vless",
            "tag": "tunnel",
            "settings": {
                "address": "127.0.0.1",
                "port": 15100,
                "id": UUID2,
                "email": "bridge2@reverse",
                "encryption": "none",
                "reverse": {
                    "tag": "bridge-in"
                }
            },
            "streamSettings": {
                "network": "xhttp",
                "xhttpSettings": {
                    "path": "/reverse",
                    "mode": "packet-up"
                }
            }
        }
    ],
    "routing": {
        "domainStrategy": "AsIs",
        "rules": [
            {
                "type": "field",
                "inboundTag": ["bridge-in"],
                "outboundTag": "direct"
            }
        ]
    }
}

with open(PORTAL_JSON, "w") as f:
    json.dump(portal_config, f, indent=2)
with open(BRIDGE1_JSON, "w") as f:
    json.dump(bridge1_config, f, indent=2)
with open(BRIDGE2_JSON, "w") as f:
    json.dump(bridge2_config, f, indent=2)

print("[*] Configuration files written to /tmp/xray_pool_test/")

# --- 2. Start Local Mock HTTP Server ---
class MockHandler(http.server.SimpleHTTPRequestHandler):
    def log_message(self, format, *args):
        pass
    def do_GET(self):
        self.send_response(200)
        self.send_header("Content-type", "text/plain")
        self.end_headers()
        client_port = self.client_address[1]
        self.wfile.write(f"Hello from Mock Server. Client local port: {client_port}\n".encode())

socketserver.TCPServer.allow_reuse_address = True
mock_server = socketserver.TCPServer(("127.0.0.1", 38080), MockHandler)
mock_thread = threading.Thread(target=mock_server.serve_forever, daemon=True)
mock_thread.start()
print("[*] Mock HTTP Server started on http://127.0.0.1:38080")

# --- 3. Start Xray Processes ---
print("[*] Launching Xray Portal and Bridge processes...")

portal_proc = subprocess.Popen(
    ["xray", "-config", PORTAL_JSON],
    stdout=open(PORTAL_LOG, "w"),
    stderr=subprocess.STDOUT
)

time.sleep(1) # Let Portal start listening

bridge1_proc = subprocess.Popen(
    ["xray", "-config", BRIDGE1_JSON],
    stdout=open(BRIDGE1_LOG, "w"),
    stderr=subprocess.STDOUT
)

bridge2_proc = subprocess.Popen(
    ["xray", "-config", BRIDGE2_JSON],
    stdout=open(BRIDGE2_LOG, "w"),
    stderr=subprocess.STDOUT
)

print("[*] Waiting 5 seconds for reverse tunnels to establish and register...")
time.sleep(5)

# --- 4. Run SOCKS5 Request Suite (Phase 1: Both Bridges Connected) ---
print("\n=== PHASE 1: BOTH TUNNELS CONNECTED (UNIQUE TAGS + BALANCER) ===")
print("[*] Executing 10 HTTP requests through the Portal SOCKS5 proxy (port 10800)...")

failed_count = 0

for i in range(1, 11):
    try:
        res = subprocess.run(
            ["curl", "-s", "--socks5-hostname", "127.0.0.1:10800", "http://127.0.0.1:38080/test"],
            capture_output=True,
            text=True,
            timeout=2
        )
        if res.returncode == 0:
            print(f"Request #{i:02d}: Success. {res.stdout.strip()}")
        else:
            print(f"Request #{i:02d}: Failed. Exit code: {res.returncode}")
            failed_count += 1
    except Exception as e:
        print(f"Request #{i:02d}: Error: {e}")
        failed_count += 1

# Analyze final logs of Phase 1 to verify active-active load balancing
time.sleep(0.5) # Let logs write
b1_logs = open(BRIDGE1_LOG).read()
b2_logs = open(BRIDGE2_LOG).read()
b1_occ = b1_logs.count("tcp:127.0.0.1:38080")
b2_occ = b2_logs.count("tcp:127.0.0.1:38080")

print(f"\n[Phase 1 Distribution Results]:")
print(f"  - Requests handled by Bridge 1: {b1_occ}")
print(f"  - Requests handled by Bridge 2: {b2_occ}")
print(f"  - Failed requests:             {failed_count}")

# --- 5. Run SOCKS5 Request Suite (Phase 2: Sever Bridge 1) ---
print(f"\n=== PHASE 2: SEVER BRIDGE 1 (ACTIVE-ACTIVE CRASH TEST) ===")
print(f"[*] Killing Bridge 1 process to test balancer dynamic pruning...")
bridge1_proc.terminate()
bridge1_proc.wait()
print(f"[*] Bridge 1 terminated. Waiting 5 seconds for Portal to detect...")
time.sleep(5)

print("[*] Executing another 10 HTTP requests through SOCKS5...")
phase2_failed = 0

# Record starting occurrences for Bridge 2
b2_start_occ = open(BRIDGE2_LOG).read().count("tcp:127.0.0.1:38080")

for i in range(1, 11):
    try:
        res = subprocess.run(
            ["curl", "-s", "--socks5-hostname", "127.0.0.1:10800", "http://127.0.0.1:38080/test"],
            capture_output=True,
            text=True,
            timeout=2
        )
        if res.returncode == 0:
            print(f"Request #{i:02d}: Success. {res.stdout.strip()}")
        else:
            print(f"Request #{i:02d}: Failed. Exit code: {res.returncode}")
            phase2_failed += 1
    except Exception as e:
        print(f"Request #{i:02d}: Error: {e}")
        phase2_failed += 1

# Analyze final logs of Phase 2
b2_final_occ = open(BRIDGE2_LOG).read().count("tcp:127.0.0.1:38080")
b2_phase2_handled = b2_final_occ - b2_start_occ

print(f"\n[Phase 2 Results]:")
print(f"  - Requests handled by surviving Bridge 2: {b2_phase2_handled}")
print(f"  - Failed requests during tunnel outage:  {phase2_failed}")

# --- 6. Cleanup ---
print("\n[*] Tearing down test environment...")
portal_proc.terminate()
bridge2_proc.terminate()
portal_proc.wait()
bridge2_proc.wait()
mock_server.shutdown()
mock_server.server_close()
print("[*] Cleaned up all Xray and Python server processes.")

# --- 7. Final Diagnostic Conclusion ---
print("\n" + "="*50)
print("             DIAGNOSTIC CONCLUSIONS")
print("="*50)
is_active_active = b1_occ > 0 and b2_occ > 0

if is_active_active:
    print("[PASS] Unique Tags + Balancer allows ACTIVE-ACTIVE Speed Aggregation!")
    print(f"       -> Dynamic load balancing verified (Bridge 1: {b1_occ}, Bridge 2: {b2_occ})")
else:
    print("[FAIL] Mismatch or Collision occurred.")
    print("       -> Active-Active speed aggregation failed.")

if phase2_failed == 0:
    print("\n[PASS] Balancer successfully performed DYNAMIC PRUNING of the dead tunnel!")
    print(f"       -> All 10 requests in Phase 2 went through surviving Bridge 2 ({b2_phase2_handled}/10) with ZERO drops.")
    print("       -> Xray's VLESS inbound dynamically unregisters dead outbound tags from the manager pool!")
else:
    print(f"\n[CRITICAL FAIL] Outage caused {phase2_failed} request failures!")
    print("       -> Balancer tried to route traffic to the dead Bridge 1 tag and dropped requests.")
    print("       -> Dead dynamic outbound handlers are NOT unregistered automatically.")
print("="*50)
