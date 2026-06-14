#!/usr/bin/env python3
import os
import subprocess
import time
import http.server
import socketserver
import threading
import json
import asyncio
import aiohttp
import uuid

TEST_DIR = "/tmp/xray_concurrency_test"
os.makedirs(TEST_DIR, exist_ok=True)

PORTAL_JSON = os.path.join(TEST_DIR, "portal.json")
BRIDGE_JSON = os.path.join(TEST_DIR, "bridge.json")
PORTAL_LOG = os.path.join(TEST_DIR, "portal.log")
BRIDGE_LOG = os.path.join(TEST_DIR, "bridge.log")

UUID_NAMESPACE = uuid.NAMESPACE_DNS
KEY = "concurrency_test"

# Generate 5 deterministic UUIDs and configurations
UUIDS = [str(uuid.uuid5(UUID_NAMESPACE, f"tunnel_{KEY}_{i:03d}")) for i in range(1, 6)]

# --- 1. Write Xray Configs ---
portal_clients = []
for i, uid_val in enumerate(UUIDS, 1):
    portal_clients.append({
        "id": uid_val,
        "email": f"tunnel_{KEY}_{i:03d}@reverse",
        "reverse": {
            "tag": f"reverse-out-{i:03d}"
        }
    })

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
                "clients": portal_clients,
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
        "balancers": [
            {
                "tag": "balancer_test",
                "selector": [
                    "reverse-out-"
                ],
                "strategy": {
                    "type": "roundRobin"
                }
            }
        ],
        "rules": [
            {
                "type": "field",
                "inboundTag": ["socks-in"],
                "balancerTag": "balancer_test"
            }
        ]
    }
}

bridge_outbounds = [
    {
        "protocol": "freedom",
        "tag": "direct"
    }
]
inbound_tags = []

for i, uid_val in enumerate(UUIDS, 1):
    bridge_tag = f"bridge_{i:03d}"
    inbound_tags.append(bridge_tag)
    
    bridge_outbounds.append({
        "protocol": "vless",
        "tag": f"tunnel_{i:03d}",
        "settings": {
            "address": "127.0.0.1",
            "port": 15100,
            "id": uid_val,
            "email": f"tunnel_{KEY}_{i:03d}@reverse",
            "encryption": "none",
            "reverse": {
                "tag": bridge_tag
            }
        },
        "streamSettings": {
            "network": "xhttp",
            "xhttpSettings": {
                "path": "/reverse",
                "mode": "packet-up"
            }
        }
    })

bridge_config = {
    "log": {
        "loglevel": "debug"
    },
    "inbounds": [],
    "outbounds": bridge_outbounds,
    "routing": {
        "domainStrategy": "AsIs",
        "rules": [
            {
                "type": "field",
                "inboundTag": inbound_tags,
                "outboundTag": "direct"
            }
        ]
    }
}

with open(PORTAL_JSON, "w") as f:
    json.dump(portal_config, f, indent=2)
with open(BRIDGE_JSON, "w") as f:
    json.dump(bridge_config, f, indent=2)

print("[*] Diagnostic configs written to /tmp/xray_concurrency_test/")

# --- 2. Start Local Mock HTTP Server ---
class MockHandler(http.server.SimpleHTTPRequestHandler):
    def log_message(self, format, *args):
        pass
    def do_GET(self):
        self.send_response(200)
        self.send_header("Content-type", "text/plain")
        self.end_headers()
        self.wfile.write(b"Mock Server Response\n")

socketserver.TCPServer.allow_reuse_address = True
mock_server = socketserver.TCPServer(("127.0.0.1", 38080), MockHandler)
mock_thread = threading.Thread(target=mock_server.serve_forever, daemon=True)
mock_thread.start()
print("[*] Local mock server running on port 38080.")

# --- 3. Start Xray Processes ---
portal_proc = subprocess.Popen(
    ["xray", "-config", PORTAL_JSON],
    stdout=open(PORTAL_LOG, "w"),
    stderr=subprocess.STDOUT
)

time.sleep(1)

bridge_proc = subprocess.Popen(
    ["xray", "-config", BRIDGE_JSON],
    stdout=open(BRIDGE_LOG, "w"),
    stderr=subprocess.STDOUT
)

print("[*] Waiting 5 seconds for all 5 tunnels to establish...")
time.sleep(5)

# --- 4. Run Concurrent Requests Suite (Phase 1) ---
async def fetch(session, req_id):
    start = time.time()
    try:
        # Use SOCKS5 proxy via aiohttp socks connector
        from aiohttp_socks import ProxyConnector
        connector = ProxyConnector.from_url("socks5://127.0.0.1:10800")
        async with aiohttp.ClientSession(connector=connector) as proxied_session:
            async with proxied_session.get("http://127.0.0.1:38080/test", timeout=3) as response:
                text = await response.text()
                elapsed = time.time() - start
                return req_id, True, text.strip(), elapsed
    except Exception as e:
        elapsed = time.time() - start
        return req_id, False, str(e), elapsed

async def run_concurrent_test(num_requests):
    tasks = []
    print(f"\n=== LAUNCHING {num_requests} CONCURRENT REQUESTS SIMULTANEOUSLY ===")
    for i in range(1, num_requests + 1):
        tasks.append(asyncio.create_task(fetch(None, i)))
    
    results = await asyncio.gather(*tasks)
    
    success_count = 0
    failure_count = 0
    total_time = 0.0
    
    for req_id, success, msg, elapsed in results:
        if success:
            success_count += 1
            total_time += elapsed
        else:
            failure_count += 1
            print(f"Request #{req_id:02d}: FAILED in {elapsed:.3f}s - {msg}")
            
    print(f"\n[Aggregation Concurrency Results]:")
    print(f"  - Total requests:    {num_requests}")
    print(f"  - Successful:        {success_count}")
    print(f"  - Failed:            {failure_count}")
    if success_count > 0:
        print(f"  - Avg Latency:       {total_time/success_count:.3f}s")

# Run the async test suite
try:
    asyncio.run(run_concurrent_test(50))
except Exception as e:
    print(f"Error executing async suite: {e}")

# --- 5. Analyze Traffic Distribution ---
time.sleep(1) # Let logs flush
portal_logs = open(PORTAL_LOG).read()

print("\n" + "="*50)
print("             TRAFFIC DISTRIBUTION ANALYSIS")
print("="*50)

total_distribution = 0
for i in range(1, 6):
    tag = f"reverse-out-{i:03d}"
    occ = portal_logs.count(f"detour [{tag}]")
    print(f"  - Tunnel {i} ({tag}): detoured {occ} times")
    total_distribution += occ

print(f"  - Total detoured streams: {total_distribution}")
print("="*50)

# --- 6. Tear Down ---
print("\n[*] Tearing down test environment...")
portal_proc.terminate()
bridge_proc.terminate()
portal_proc.wait()
bridge_proc.wait()
mock_server.shutdown()
mock_server.server_close()
print("[*] Cleaned up.")
