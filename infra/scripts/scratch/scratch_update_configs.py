import json
import sys
import os

def update_srv03_portal():
    old_file = '/usr/local/etc/xray/c-01-01-03-01.json'
    new_file = '/usr/local/etc/xray/11-01-03-01.json'
    if not os.path.exists(old_file):
        print(f"File {old_file} not found. Skipping or already done.")
        return
    with open(old_file, 'r') as f:
        d = json.load(f)
    d['inbounds'][0]['listen'] = '0.0.0.0'
    d['inbounds'][0]['streamSettings']['xhttpSettings']['path'] = '/11-01-03-01'
    with open(new_file, 'w') as f:
        json.dump(d, f, indent=4)
    os.remove(old_file)
    print("Server 03 portal config updated successfully.")

def update_srv09_bridge():
    fpath = '/usr/local/etc/xray/11-01-03-01.json'
    if not os.path.exists(fpath):
        print(f"File {fpath} not found.")
        return
    with open(fpath, 'r') as f:
        d = json.load(f)
    d['outbounds'][1]['streamSettings']['xhttpSettings']['path'] = '/11-01-03-01'
    with open(fpath, 'w') as f:
        json.dump(d, f, indent=4)
    print("Server 09 bridge config updated successfully.")

def update_srv01_portals():
    files = ['/usr/local/etc/xray/01-01-01-01.json', '/usr/local/etc/xray/01-01-05.json', '/usr/local/etc/xray/01-01-01-05.json']
    for fp in files:
        if not os.path.exists(fp):
            continue
        with open(fp, 'r') as f:
            d = json.load(f)
        d['inbounds'][0]['listen'] = '0.0.0.0'
        with open(fp, 'w') as f:
            json.dump(d, f, indent=4)
        print(f"Server 01 portal {fp} updated to listen on 0.0.0.0.")

def update_srv04_portals():
    files = ['/usr/local/etc/xray/16-01-04-01.json', '/usr/local/etc/xray/20-01-04-05.json']
    for fp in files:
        if not os.path.exists(fp):
            continue
        with open(fp, 'r') as f:
            d = json.load(f)
        d['inbounds'][0]['listen'] = '0.0.0.0'
        with open(fp, 'w') as f:
            json.dump(d, f, indent=4)
        print(f"Server 04 portal {fp} updated to listen on 0.0.0.0.")

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print("Usage: python3 update_configs.py [action]")
        sys.exit(1)
    action = sys.argv[1]
    if action == 'srv03':
        update_srv03_portal()
    elif action == 'srv09':
        update_srv09_bridge()
    elif action == 'srv01':
        update_srv01_portals()
    elif action == 'srv04':
        update_srv04_portals()
    else:
        print(f"Unknown action {action}")
