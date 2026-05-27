import json
import subprocess
import time
import os

# Define the scenarios to test
SCENARIOS = {
    "Scenario_A_HighSecurity": {
        "mode": "packet-up",
        "maxConcurrency": 8,
        "xPaddingBytes": "500-1500",
        "xPaddingObfsMode": True
    },
    "Scenario_B_MediumSecurity": {
        "mode": "packet-up",
        "maxConcurrency": 32,
        "xPaddingBytes": "100-300",
        "xPaddingObfsMode": True
    },
    "Scenario_C_NoPadding": {
        "mode": "packet-up",
        "maxConcurrency": 128,
        "xPaddingBytes": "0-0",
        "xPaddingObfsMode": False
    },
    "Scenario_D_StreamUp": {
        "mode": "stream-up",
        "maxConcurrency": 64,
        "xPaddingBytes": "0-0",
        "xPaddingObfsMode": False
    }
}

PORTAL_TEMPLATE_PATH = "configs/xray/generated/portal_cdn_loopback_srv04.json"
BRIDGE_TEMPLATE_PATH = "configs/xray/generated/bridge_cdn_loopback_srv04.json"

PORTAL_REMOTE = "/usr/local/etc/xray/portal_cdn_loopback.json"
BRIDGE_REMOTE = "/usr/local/etc/xray/bridge_local_loopback.json"

SSH_BASE = 'ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no -i C:\\Users\\MeRezaRezaei\\.ssh\\id_rsa_no_p root@185.204.197.242'
JUMP_SSH = 'sshpass -p asdfjkl ssh -o StrictHostKeyChecking=no -o ConnectTimeout=5 root@10.255.1.4'

def run_local(cmd):
    res = subprocess.run(cmd, shell=True, capture_output=True, text=True)
    return res.returncode, res.stdout, res.stderr

def run_remote_srv04(cmd):
    full_cmd = f'{SSH_BASE} "{JUMP_SSH} \'{cmd}\'"'
    res = subprocess.run(full_cmd, shell=True, capture_output=True, text=True)
    return res.returncode, res.stdout, res.stderr

def upload_configs():
    run_local(f'scp -o ConnectTimeout=5 -o StrictHostKeyChecking=no -i C:\\Users\\MeRezaRezaei\\.ssh\\id_rsa_no_p configs/xray/generated/portal_cdn_loopback_srv04.json root@185.204.197.242:/tmp/portal_cdn_loopback.json')
    run_local(f'scp -o ConnectTimeout=5 -o StrictHostKeyChecking=no -i C:\\Users\\MeRezaRezaei\\.ssh\\id_rsa_no_p configs/xray/generated/bridge_cdn_loopback_srv04.json root@185.204.197.242:/tmp/bridge_local_loopback.json')
    run_remote_srv04(f'cp /tmp/portal_cdn_loopback.json {PORTAL_REMOTE}')
    run_remote_srv04(f'cp /tmp/bridge_local_loopback.json {BRIDGE_REMOTE}')

def restart_xray():
    run_remote_srv04("pkill -f portal_cdn_loopback.json")
    run_remote_srv04("pkill -f bridge_local_loopback.json")
    time.sleep(1)
    run_remote_srv04(f"nohup xray run --config {PORTAL_REMOTE} >/var/log/xray_portal_loopback.log 2>&1 < /dev/null &")
    run_remote_srv04(f"nohup xray run --config {BRIDGE_REMOTE} >/var/log/xray_bridge_loopback.log 2>&1 < /dev/null &")
    time.sleep(3)

def run_benchmark():
    latencies = []
    successes = 0
    # Run 10 requests to domestic target for higher accuracy
    for i in range(10):
        start_time = time.time()
        ret, stdout, stderr = run_remote_srv04("curl -s -o /dev/null -w '%{http_code}' --socks5-hostname 127.0.0.1:10800 https://iran.ir --max-time 5")
        end_time = time.time()
        duration = end_time - start_time
        
        http_code = stdout.strip()
        # iran.ir returns 302 or 200 depending on redirects
        if ret == 0 and http_code in ["200", "302"]:
            latencies.append(duration)
            successes += 1
        time.sleep(0.2)
    
    avg_latency = sum(latencies) / len(latencies) if latencies else 0
    return successes, avg_latency

def main():
    print("Starting raw DOMESTIC loopback benchmark...")
    
    with open(PORTAL_TEMPLATE_PATH, "r") as f:
        portal_config = json.load(f)
    with open(BRIDGE_TEMPLATE_PATH, "r") as f:
        bridge_config = json.load(f)
        
    results = {}
    
    for name, params in SCENARIOS.items():
        print(f"\n--- Running Scenario: {name} ---")
        
        portal_config["inbounds"][0]["streamSettings"]["xhttpSettings"]["mode"] = params["mode"]
        portal_config["inbounds"][0]["streamSettings"]["xhttpSettings"]["extra"]["xPaddingBytes"] = params["xPaddingBytes"]
        portal_config["inbounds"][0]["streamSettings"]["xhttpSettings"]["extra"]["xPaddingObfsMode"] = params["xPaddingObfsMode"]
        
        bridge_config["outbounds"][2]["streamSettings"]["xhttpSettings"]["mode"] = params["mode"]
        bridge_config["outbounds"][2]["streamSettings"]["xhttpSettings"]["extra"]["xPaddingBytes"] = params["xPaddingBytes"]
        bridge_config["outbounds"][2]["streamSettings"]["xhttpSettings"]["extra"]["xPaddingObfsMode"] = params["xPaddingObfsMode"]
        bridge_config["outbounds"][2]["streamSettings"]["xhttpSettings"]["extra"]["xmux"]["maxConcurrency"] = params["maxConcurrency"]
        
        with open(PORTAL_TEMPLATE_PATH, "w") as f:
            json.dump(portal_config, f, indent=2)
        with open(BRIDGE_TEMPLATE_PATH, "w") as f:
            json.dump(bridge_config, f, indent=2)
            
        upload_configs()
        restart_xray()
        
        successes, avg_latency = run_benchmark()
        print(f"Result for {name}: Successes: {successes}/10, Avg Latency: {avg_latency:.3f}s")
        
        results[name] = {
            "successes": successes,
            "avg_latency": avg_latency
        }
        
    print("\nDomestic Benchmark Complete! Results:")
    print("| Scenario | Success Rate | Average Latency |")
    print("|---|---|---|")
    for name, data in results.items():
        rate = f"{data['successes']}/10"
        latency = f"{data['avg_latency']:.3f}s" if data['successes'] > 0 else "N/A"
        print(f"| {name} | {rate} | {latency} |")

if __name__ == "__main__":
    main()
