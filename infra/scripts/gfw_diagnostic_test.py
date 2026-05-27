#!/usr/bin/env python3
import socket
import ssl
import time
import sys
import argparse
import urllib.request
import urllib.error
import sys

# ==============================================================================
# CRITICAL SECURITY MANDATE: SERVER 07 ABSOLUTE ZERO-TOUCH POLICY
# ==============================================================================
# Server 07 is the core, irreplaceable "Pro" management gateway.
# Under no circumstances should this script or any tool touch Server 07.
# Targets starting with i-07, or IPs 185.204.197.242, 10.255.1.7 are strictly banned.
# ==============================================================================
BANNED_IDENTIFIERS = ["185.204.197.242", "10.255.1.7", "i-07"]

def enforce_safety_mandate(target_ip, sni_domain, url_target):
    for banned in BANNED_IDENTIFIERS:
        if banned in str(target_ip) or banned in str(sni_domain) or banned in str(url_target):
            print(f"\n[FATAL ERROR] SECURITY MANDATE VIOLATION: Attempted to touch Server 07 ({banned})!")
            print("Server 07 is strictly locked. Execution aborted immediately to prevent network lockout.\n")
            sys.exit(1)

def test_sni_handshake(target_ip, target_port, sni_domain, timeout=5):
    """
    Simulates a raw TLS handshake to check if GFW blocks or delays
    the handshake based on the plaintext SNI domain.
    """
    print(f"[*] Testing TLS Handshake to {target_ip}:{target_port} with SNI '{sni_domain}'...")
    
    # Create raw TCP socket
    sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    sock.settimeout(timeout)
    
    # Create default SSL context (TLS 1.3/1.2)
    context = ssl.create_default_context()
    context.check_hostname = False
    context.verify_mode = ssl.CERT_NONE  # Self-signed certs bypass
    
    start_time = time.time()
    try:
        sock.connect((target_ip, target_port))
        tcp_time = time.time() - start_time
        print(f"    [+] TCP Connection established in {tcp_time:.4f}s")
        
        # Wrap socket in TLS
        tls_sock = context.wrap_socket(sock, server_hostname=sni_domain)
        tls_time = time.time() - (start_time + tcp_time)
        print(f"    [+] TLS Handshake completed in {tls_time:.4f}s")
        print(f"    [+] Cipher negotiated: {tls_sock.cipher()}")
        tls_sock.close()
        return True, tcp_time, tls_time
    except socket.timeout:
        print(f"    [-] Connection TIMEOUT during handshake.")
        return False, None, None
    except ssl.SSLError as e:
        print(f"    [-] TLS Handshake FAILED: {e}")
        return False, None, None
    except Exception as e:
        print(f"    [-] Connection FAILED: {e}")
        return False, None, None
    finally:
        sock.close()

def test_throughput_throttling(cdn_url, duration_secs=30):
    """
    Downloads data over HTTPS from the CDN domain for a duration
    and logs throughput every second to detect if/when GFW throttling kicks in.
    """
    print(f"[*] Testing Throughput & Throttling behavior over CDN: {cdn_url}")
    print(f"[*] Running test for {duration_secs} seconds to observe flow-analysis drop...")
    
    start_time = time.time()
    bytes_downloaded = 0
    interval_bytes = 0
    last_interval_time = start_time
    
    try:
        req = urllib.request.Request(
            cdn_url, 
            headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'}
        )
        with urllib.request.urlopen(req, timeout=10) as response:
            print(f"    [+] Connected successfully. HTTP Status: {response.status}")
            
            while True:
                chunk = response.read(16384) # 16KB chunks
                if not chunk:
                    break
                
                bytes_downloaded += len(chunk)
                interval_bytes += len(chunk)
                
                current_time = time.time()
                elapsed = current_time - start_time
                if elapsed > duration_secs:
                    break
                
                # Report speed every second
                if current_time - last_interval_time >= 1.0:
                    speed_mbps = (interval_bytes * 8) / (1024 * 1024 * (current_time - last_interval_time))
                    total_mb = bytes_downloaded / (1024 * 1024)
                    print(f"    {elapsed:.1f}s | Current Speed: {speed_mbps:.2f} Mbps | Total Downloaded: {total_mb:.2f} MB")
                    interval_bytes = 0
                    last_interval_time = current_time
                    
    except urllib.error.URLError as e:
        print(f"    [-] Connection Failed or Interrupted: {e}")
    except socket.timeout:
        print(f"    [-] Connection TIMEOUT during active streaming.")
    except Exception as e:
        print(f"    [-] Streaming Interrupted: {e}")
        
    total_time = time.time() - start_time
    avg_speed_mbps = (bytes_downloaded * 8) / (1024 * 1024 * total_time) if total_time > 0 else 0
    print(f"[*] Test Finished in {total_time:.2f}s. Average Speed: {avg_speed_mbps:.2f} Mbps. Total Data: {bytes_downloaded / (1024 * 1024):.2f} MB")

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="GFW & INI Border Gateway Diagnostic Tester")
    parser.add_argument("--mode", choices=["handshake", "throughput", "all"], default="all", help="Test mode")
    parser.add_argument("--ip", default="95.38.180.145", help="Target public IP inside Iran (Server 01)")
    parser.add_argument("--port", type=int, default=443, help="Target TLS Port")
    parser.add_argument("--sni", default="i-01.doctel.ir", help="SNI Domain name to test (Server 01 domain)")
    parser.add_argument("--url", default="https://i-01.doctel.ir/healthcheck", help="HTTPS URL for throughput testing (Server 01 healthcheck)")
    parser.add_argument("--time", type=int, default=15, help="Throughput test duration in seconds")
    
    args = parser.parse_args()
    
    enforce_safety_mandate(args.ip, args.sni, args.url)
    
    if args.mode in ["handshake", "all"]:
        # Test 1: Real SNI
        test_sni_handshake(args.ip, args.port, args.sni)
        print("-" * 50)
        # Test 2: Spoofed Bank SNI (should bypass SNI blocks if SNI-only filtering is active)
        test_sni_handshake(args.ip, args.port, "asan.shaparak.ir")
        print("-" * 50)
        # Test 3: Spoofed foreign blocked SNI (should trigger instant block/reset)
        test_sni_handshake(args.ip, args.port, "www.youtube.com")
        print("=" * 50)
        
    if args.mode in ["throughput", "all"]:
        test_throughput_throttling(args.url, args.time)
