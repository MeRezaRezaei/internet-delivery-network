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
import sys

# Define test directories and configuration paths
TEST_DIR = "/tmp/xray_mux_concurrency_test"
os.makedirs(TEST_DIR, exist_ok=True)

PORTAL_JSON = os.path.join(TEST_DIR, "portal.json")
BRIDGE_JSON = os.path.join(TEST_DIR, "bridge.json")
CLIENT_JSON = os.path.join(TEST_DIR, "client.json")

PORTAL_LOG = os.path.join(TEST_DIR, "portal.log")
BRIDGE_LOG = os.path.join(TEST_DIR, "bridge.log")
CLIENT_LOG = os.path.join(TEST_DIR, "client.log")

UUID_NAMESPACE = uuid.NAMESPACE_DNS
KEY = "mux_concurrency_test"

# Generate 5 deterministic UUIDs for Bridge reverse tunnels
REVERSE_UUIDS = [str(uuid.uuid5(UUID_NAMESPACE, f"tunnel_{KEY}_{i:03d}")) for i in range(1, 6)]

# Single static UUID for Client-to-Portal VLESS connection
CLIENT_UUID = "c8f56fa8-4b7b-40fa-8a21-72f10b2f5d72"

def write_configs(mux_enabled):
    # 1. Portal Config
    portal_clients = []
    for i, uid_val in enumerate(REVERSE_UUIDS, 1):
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
            # Client-facing VLESS inbound
            {
                "tag": "vless-client-in",
                "port": 10800,
                "listen": "127.0.0.1",
                "protocol": "vless",
                "settings": {
                    "clients": [
                        {
                            "id": CLIENT_UUID,
                            "email": "user@client"
                        }
                    ],
                    "decryption": "none"
                }
            },
            # VLESS Reverse Portal Listener receiving the bridge connections
            {
                "tag": "vless-reverse-in",
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
                    "inboundTag": ["vless-client-in"],
                    "balancerTag": "balancer_test"
                }
            ]
        }
    }

    # 2. Bridge Config
    bridge_outbounds = [
        {
            "protocol": "freedom",
            "tag": "direct"
        }
    ]
    inbound_tags = []
    for i, uid_val in enumerate(REVERSE_UUIDS, 1):
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

    # 3. Client Config
    client_vless_outbound = {
        "protocol": "vless",
        "tag": "vless-out",
        "settings": {
            "vnext": [
                {
                    "address": "127.0.0.1",
                    "port": 10800,
                    "users": [
                        {
                            "id": CLIENT_UUID,
                            "encryption": "none"
                        }
                    ]
                }
            ]
        }
    }
    
    if mux_enabled:
        client_vless_outbound["mux"] = {
            "enabled": True,
            "concurrency": 8
        }
        
    client_config = {
        "log": {
            "loglevel": "debug"
        },
        "inbounds": [
            {
                "tag": "socks-in",
                "port": 10900,
                "listen": "127.0.0.1",
                "protocol": "socks",
                "settings": {
                    "auth": "noauth",
                    "udp": True
                }
            }
        ],
        "outbounds": [
            client_vless_outbound
        ],
        "routing": {
            "domainStrategy": "AsIs",
            "rules": [
                {
                    "type": "field",
                    "inboundTag": ["socks-in"],
                    "outboundTag": "vless-out"
                }
            ]
        }
    }

    with open(PORTAL_JSON, "w") as f:
        json.dump(portal_config, f, indent=2)
    with open(BRIDGE_JSON, "w") as f:
        json.dump(bridge_config, f, indent=2)
    with open(CLIENT_JSON, "w") as f:
        json.dump(client_config, f, indent=2)

# --- Start Local Mock HTTP Server ---
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

# --- Async HTTP SOCKS Client ---
async def fetch(session, req_id):
    start = time.time()
    try:
        from aiohttp_socks import ProxyConnector
        connector = ProxyConnector.from_url("socks5://127.0.0.1:10900")
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
    return success_count, failure_count, total_time

def run_experiment(mux_enabled):
    print(f"\n==================================================")
    print(f"      RUNNING EXPERIMENT: CLIENT MUX = {mux_enabled}")
    print(f"==================================================")
    
    # 1. Clean up logs and write configs
    for f_path in [PORTAL_LOG, BRIDGE_LOG, CLIENT_LOG]:
        if os.path.exists(f_path):
            os.remove(f_path)
            
    write_configs(mux_enabled)
    
    # 2. Start Xray Processes
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
    time.sleep(1)
    
    client_proc = subprocess.Popen(
        ["xray", "-config", CLIENT_JSON],
        stdout=open(CLIENT_LOG, "w"),
        stderr=subprocess.STDOUT
    )
    
    print("[*] Waiting 5 seconds for tunnels and client sessions to establish...")
    time.sleep(5)
    
    # 3. Execute Async Requests
    print("[*] Dispatching 50 concurrent requests simultaneously through SOCKS...")
    success, failure, total_time = asyncio.run(run_concurrent_test(50))
    print(f"    - Requests: 50 | Success: {success} | Failed: {failure}")
    if success > 0:
        print(f"    - Avg Latency: {total_time/success:.3f}s")
        
    # 4. Tear down Xray processes
    portal_proc.terminate()
    bridge_proc.terminate()
    client_proc.terminate()
    portal_proc.wait()
    bridge_proc.wait()
    client_proc.wait()
    
    # 5. Analyze distribution from portal logs
    time.sleep(1)
    portal_logs = open(PORTAL_LOG).read()
    
    print("\n  [TUNNEL DISTRIBUTION ANALYSIS]:")
    total_distribution = 0
    distribution = {}
    for i in range(1, 6):
        tag = f"reverse-out-{i:03d}"
        occ = portal_logs.count(f"detour [{tag}]")
        print(f"    - Tunnel {i} ({tag}): detoured {occ} times")
        distribution[i] = occ
        total_distribution += occ
    print(f"    - Total detoured streams: {total_distribution}")
    
    # Determine aggregation efficiency
    non_zero_tunnels = sum(1 for v in distribution.values() if v > 0)
    print(f"\n  [AGGREGATION EFFICIENCY]:")
    print(f"    - Active tunnels used: {non_zero_tunnels} / 5")
    
    if non_zero_tunnels > 1:
        print(f"    - Result: SUCCESS! True Active-Active Speed Aggregation achieved!")
        print(f"              Sub-connections demultiplexed and distributed across multiple physical streams.")
    else:
        print(f"    - Result: CHOKE! Traffic stuck on a SINGLE tunnel.")
        print(f"              All multiplexed requests forced down a single stream, destroying speed aggregation.")
        
    return distribution

if __name__ == "__main__":
    # Run both experiments sequentially to compare and prove
    dist_no_mux = run_experiment(mux_enabled=False)
    dist_with_mux = run_experiment(mux_enabled=True)
    
    # Print Final Summary Comparison Table
    print("\n" + "="*60)
    print("             FINAL COMPARISON & EXPERIMENT SUMMARY")
    print("="*60)
    print(" Tunnel Tag      | Mux Disabled Detours | Mux Enabled Detours ")
    print("-"*60)
    for i in range(1, 6):
        tag = f"reverse-out-{i:03d}"
        print(f" {tag:<15} | {dist_no_mux[i]:^20} | {dist_with_mux[i]:^19} ")
    print("="*60)
    
    print("\n[*] Shutting down mock HTTP server...")
    mock_server.shutdown()
    mock_server.server_close()
    print("[*] Experiment complete.")
