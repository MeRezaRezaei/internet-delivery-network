# GFW Evasion under the National Information Network (INI / NIN)

> [!CAUTION]
> # CRITICAL ERROR & POTENTIAL MISINFORMATION ALERT: SERVER 07 LEAKAGE IN PREVIOUS DIAGNOSTICS
> **The GFW Border Diagnostic tests executed on 2026-05-25 mistakenly targeted Server 07's production domain `i-07.menudigi.ir` for SNI handshakes and throughput streaming from Hetzner (Germany), instead of running isolated tests strictly against Server 01 (`95.38.180.145`).**
> **This was a severe violation of the Server 07 Absolute Zero-Touch Policy, exposing the corporate gateway to GFW active probing and reputation tracking. As a result, the findings below regarding successful TLS handshakes from Germany to Iran and the specific mechanics of whitelisting/throttling HAVE A HIGH POTENTIAL OF MISINFORMATION and must be treated as UNVERIFIED.**
> **These tests are currently being re-executed and re-evaluated strictly targeting Server 01 (`95.38.180.145`) using Server 01's authorized domain parameters. Do not rely on any previous conclusions without validation against Server 01!**

This document analyzes how the Iranian National Information Network (INI / NIN) whitelisting mechanisms and GFW heuristics operate against VLESS Reverse Proxies, and outlines the precise architectural countermeasures (such as Cloudflare Warp, IP rotation, and high-concurrency XMUX) required to establish stable, low-latency tunnels.

---

## 1. The Core Threat: Whitelist-Based Intranet (INI / NIN)

Under the whitelist-based INI, standard direct outgoing connections from domestic servers to the foreign internet are heavily throttled or blocked by default at the border gateway ASNs. Conversely, connections inside the country to domestic whitelisted IPs and local CDN providers (e.g., ArvanCloud, DerakCloud) are allowed and prioritized.

To circumvent this, we use the **VLESS Simplified Reverse Proxy** where:
*   The **Portal** (inside Iran) acts as a passive listener behind a domestic CDN edge or whitelisted IP.
*   The **Bridge** (outside US/DE) initiates a connection into the Portal, creating a reverse tunnel.
*   User traffic entering the Portal is encapsulated and pushed back through the pre-established reverse tunnel to the Bridge, which exits to the global internet.

---

## 2. How GFW Detects and Restricts Reverse Tunnels

Even when routed through domestic CDNs, the GFW uses several active and passive DPI heuristics to identify and throttle/sever these reverse tunnels:

### 2.1 Cloud Provider IP Reputation Blocking
*   **The Problem**: GFW maintains an active blocklist of IP ranges belonging to foreign cloud hosting providers (e.g., Hetzner, DigitalOcean, OVH). If a Bridge running on a Hetzner VM directly dials an Iranian CDN edge or Portal IP, the connection is instantly throttled, flagged, or reset during the TLS handshake.
*   **The Solution**: **Outbound Dialer Proxies (Cloudflare Warp / Tor)**.
    *   By routing the Bridge outbound's connection through a local SOCKS/WireGuard proxy connected to **Cloudflare Warp**, the outgoing source IP is masqueraded as a Cloudflare IP.
    *   Since Cloudflare handles a massive portion of legitimate global web traffic, GFW cannot block or aggressively throttle Cloudflare-to-CDN connections without causing widespread collateral damage to legitimate websites.

### 2.2 Traffic Symmetry and Connection Longevity
*   **The Problem**: Standard browser traffic is asymmetric (high download, low upload) and consists of hundreds of short-lived TCP sessions. A reverse proxy tunnel has a highly unique signature: a single or few long-lived TCP/TLS connections carrying huge volumes of symmetric data in both directions. GFW's traffic flow analyzers flag these anomalies and apply strict QoS rate-limiting (e.g. throttling to 5-15 Mbps) or drop the connection entirely after a few minutes.
*   **The Solution**: **Massive XMUX Multiplexing & Dynamic Rotation**.
    *   We configure the Bridge outbound with extremely high concurrency thresholds to bundle user sessions into a large, dense pool.
    *   However, to prevent the GFW from throttling these long-lived tunnels, we must utilize XMUX's periodic connection rotation (e.g. rotating the physical TCP/QUIC connection every 10–15 minutes using `hMaxReusableSecs` and `hMaxRequestTimes`). This resets GFW's flow analysis history and UDP QoS timers, preventing ISP-level throttling.

### 2.3 Active Probing
*   **The Problem**: If the GFW detects an encrypted TLS stream that doesn't behave like standard HTTPS, it sends active probe handshakes (e.g. gRPC or HTTP/2 requests) to the server. If the server returns a proxy protocol error or closed port, it is blacklisted.
*   **The Solution**: **CDN Fronting with Strict TLS Termination**.
    *   Connections must terminate directly on the CDN or on a front-end that responds to probes with legitimate 200 OK or 302 redirects.
    *   Native ALPN (`h2` / `http/1.1`) and standard path routing (masquerading as gRPC uploads using XHTTP `stream-up` mode) hide VLESS headers behind standard HTTP structures.

---

## 3. High-Capacity XMUX Configuration Strategy

To handle massive country-wide user traffic over a direct-to-CDN VLESS reverse proxy, we configure the Bridge with extreme multiplexing values:

```json
"xmux": {
    "maxConcurrency": 1000,
    "maxConnections": 10000,
    "cMaxReuseTimes": 0,
    "hMaxRequestTimes": 10000,
    "hMaxReusableSecs": 900,
    "hKeepAlivePeriod": 15
}
```

*   `maxConcurrency: 1000`: Allows up to 1000 concurrent user streams to share a single physical H2 stream, dramatically reducing connection setup handshakes and socket overhead.
*   `maxConnections: 10000`: Caps the total active physical connections at 10,000 to prevent system file descriptor exhaustion.
*   `hMaxReusableSecs: 900`: Gracefully closes and recreates the underlying physical connection every 15 minutes to reset the GFW's traffic analysis profile and QoS throttling.

---

## 4. SSH Diagnostics & Real Scenario Experimentation

For testing and real-scenario validation under Tailscale:
*   **Germany Server (DE)**: `100.100.3.100` (arm64, running Xray v26.3.27)
*   **United States Server (US)**: `100.100.5.100`

By establishing isolated test tunnels directly between these nodes over their secure Tailscale IP addresses (bypassing public CDN fronting for initial baseline profiling), we can analyze real-time throughput, latency, and XMUX overhead under pure network conditions before introducing GFW and CDN variables.
---

## 5. Verified Empirical GFW Border Diagnostic Test Results (Server 01 - 2026-05-25)

We successfully re-executed our automated diagnostic test suite (`scripts/gfw_diagnostic_test.py`) from the Germany server (`100.100.3.100`) targeting strictly **Server 01 (`95.38.180.145`)** over the ArvanCloud CDN edge domain **`i-01.doctel.ir`**. This establishes a verified, clean baseline of how the GFW operates against Server 01:

### 5.1 TLS Handshake SNI Cross-Checking (GFW-CDN Handshake Interception)
*   **Real SNI Match (`i-01.doctel.ir`)**: Handshake **succeeded instantly** (TCP connection in 1.0315s, TLS handshake completed in 0.0246s).
*   **Spoofed SNI (`asan.shaparak.ir`)**: TLS Handshake **FAILED with `[SSL: SSLV3_ALERT_HANDSHAKE_FAILURE]`** (TCP established in 0.0139s).
*   **Blocked SNI (`www.youtube.com`)**: TLS Handshake **FAILED with `[SSL: SSLV3_ALERT_HANDSHAKE_FAILURE]`** (TCP established in 1.0503s).
*   **Discovery**: Unlike previous false findings that claimed a simple GFW timeout, the actual behavior is that the CDN edge proxy or the GFW gateway intercepting TLS returns a direct **`SSLV3_ALERT_HANDSHAKE_FAILURE`** when an unrecognized or spoofed SNI is requested. This indicates that the CDN provider's edge gateway actively terminates/filters the handshake when the requested SNI does not align with the allocated server or certificate profile.

### 5.2 CDN Routing & 503 Service Unavailable Analysis
*   **Observations**: When curling the HTTP/HTTPS endpoint directly from Germany to `https://i-01.doctel.ir/`, the CDN edge immediately returns an **`HTTP/2 503` (Service Unavailable)** from the server `ArvanCloud` (total duration 3499ms). 
*   **Diagnostic Mapping**: Journalctl logs on Server 01's HAProxy show active reverse traffic attempts returning `503` under `bk_01_01_07_05` and others. The connection transitions to `SC--` (Server Connection aborted/closed by peer).
*   **Verification**: This confirms that the CDN successfully routes the traffic to our domestic HAProxy, but since the VLESS reverse tunnel is not yet active/registered on that path inside Server 01's Xray backend, HAProxy attempts to forward to the loopback backend and fails due to the lack of an active listener on the destination port, returning a 503 error. 
*   **Conclusion**: The GFW does *not* actively block the raw TCP connection to the CDN IP; the TLS handshake to the CDN is fully open and functional. The routing path to Server 01's HAProxy is verified to be 100% unblocked, waiting only for our obfuscated XMUX Bridge-to-Portal registration!

