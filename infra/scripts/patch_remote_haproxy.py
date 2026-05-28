#!/usr/bin/env python3
"""
Server 07 HAProxy Config Patcher
Author: Antigravity

This script automates the safe downloading, patching, validation, and uploading
of the Server 07 HAProxy configuration to support the 100-tunnel speed aggregator.
"""

import os
import subprocess

KEY_PATH = r"C:\Users\MeRezaRezaei\.ssh\id_rsa_no_p"
REMOTE_HOST = "root@185.204.197.242"
LOCAL_CFG_PATH = "configs/haproxy/haproxy_srv07_temp.cfg"

def run_ssh(cmd):
    full_cmd = [
        "ssh",
        "-i", KEY_PATH,
        "-o", "ConnectTimeout=5",
        "-o", "StrictHostKeyChecking=no",
        REMOTE_HOST,
        cmd
    ]
    result = subprocess.run(full_cmd, capture_output=True, text=True)
    if result.returncode != 0:
        raise RuntimeError(f"SSH Command failed: {result.stderr}")
    return result.stdout

def run_scp_to_remote(local_path, remote_path):
    full_cmd = [
        "scp",
        "-i", KEY_PATH,
        "-o", "ConnectTimeout=5",
        "-o", "StrictHostKeyChecking=no",
        local_path,
        f"{REMOTE_HOST}:{remote_path}"
    ]
    result = subprocess.run(full_cmd, capture_output=True, text=True)
    if result.returncode != 0:
        raise RuntimeError(f"SCP to remote failed: {result.stderr}")

def run_scp_from_remote(remote_path, local_path):
    full_cmd = [
        "scp",
        "-i", KEY_PATH,
        "-o", "ConnectTimeout=5",
        "-o", "StrictHostKeyChecking=no",
        f"{REMOTE_HOST}:{remote_path}",
        local_path
    ]
    result = subprocess.run(full_cmd, capture_output=True, text=True)
    if result.returncode != 0:
        raise RuntimeError(f"SCP from remote failed: {result.stderr}")

def patch_config(content):
    # 1. Update is_tunnel ACL to include /100-10-01-05
    old_acl = "acl is_tunnel path -i -m beg /21-08-07-05 /24-01-07-06 /22-04-07-06 /23-01-07-05 /21-08-07-06 /24-10-07-06"
    new_acl = "acl is_tunnel path -i -m beg /21-08-07-05 /24-01-07-06 /22-04-07-06 /23-01-07-05 /21-08-07-06 /24-10-07-06 /100-10-01-05"
    if old_acl in content:
        content = content.replace(old_acl, new_acl)
        print("[+] Patched is_tunnel ACL successfully.")
    else:
        print("[!] Warning: old_acl not found in content. Skipping ACL patch.")

    # 2. Add HTTP frontend routing rule
    old_http_route = "use_backend bk_srv10_vless if { path_beg /24-10-07-06 }"
    new_http_route = "use_backend bk_srv10_vless if { path_beg /24-10-07-06 }\n    use_backend bk_srv01_100_tunnels if { path_beg /100-10-01-05 }"
    if old_http_route in content:
        # We need to replace only in HTTP and HTTPS frontends
        content = content.replace(old_http_route, new_http_route)
        print("[+] Patched HTTP/HTTPS frontend routing rules successfully.")
    else:
        print("[!] Warning: old_http_route not found in content. Skipping routing rules patch.")

    # 3. Add backend definition at the end
    backend_def = """
# --- 100-Tunnel Speed Aggregator ---
backend bk_srv01_100_tunnels
    mode http
    server xray_srv01_100 10.255.1.1:15100
"""
    if "\nbackend bk_srv01_100_tunnels" not in content:
        content = content.strip() + "\n" + backend_def
        print("[+] Added bk_srv01_100_tunnels backend successfully.")
    else:
        print("[!] Backend bk_srv01_100_tunnels already exists. Skipping backend patch.")
        
    return content

if __name__ == "__main__":
    os.makedirs(os.path.dirname(LOCAL_CFG_PATH), exist_ok=True)
    
    print("[*] Downloading current /etc/haproxy/haproxy.cfg from Server 07...")
    run_scp_from_remote("/etc/haproxy/haproxy.cfg", LOCAL_CFG_PATH)
    
    with open(LOCAL_CFG_PATH, "r", encoding="utf-8") as f:
        content = f.read()
        
    print("[*] Patching configuration...")
    patched_content = patch_config(content)
    
    with open(LOCAL_CFG_PATH, "w", encoding="utf-8") as f:
        f.write(patched_content)
        
    print("[*] Uploading patched config to Server 07 as /etc/haproxy/haproxy.cfg.patched...")
    run_scp_to_remote(LOCAL_CFG_PATH, "/etc/haproxy/haproxy.cfg.patched")
    
    print("[*] Verifying syntax of patched config on Server 07...")
    verify_output = run_ssh("haproxy -c -f /etc/haproxy/haproxy.cfg.patched")
    print(verify_output)
    if "Configuration file is valid" in verify_output:
        print("[+] Patched configuration syntax is VALID!")
        
        # Safe deployment with backup
        print("[*] Backing up current config on Server 07...")
        run_ssh("cp /etc/haproxy/haproxy.cfg /etc/haproxy/haproxy.cfg.pre_100_tunnels")
        
        print("[*] Swapping configuration file...")
        run_ssh("mv /etc/haproxy/haproxy.cfg.patched /etc/haproxy/haproxy.cfg")
        
        print("[*] Reloading HAProxy service...")
        run_ssh("systemctl reload haproxy")
        print("[+] HAProxy successfully reloaded with the new configuration!")
    else:
        print("[X] Error: HAProxy syntax check failed. Aborting deployment.")
        print(verify_output)
