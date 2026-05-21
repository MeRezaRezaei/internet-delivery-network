# Toolset Orchestration and Architecture (IDN)

## 1. High-Level Architecture
The Internet Delivery Network (IDN) operates on a tiered architecture bridging external unrestricted servers (Bridges) to internal Iranian servers (Portals).

**Traffic Flow:**
`Global Internet` <-> `Bridge (srv08/09)` <-> `CDN (Cloudflare/Arvan)` <-> `HAProxy (srv07)` <-> `Xray Portal (srv07)` <-> `Internal Mesh (01, 04, etc.)`

---

## 2. Component Roles

### A. CDNs (Cloudflare & ArvanCloud)
- **Role:** Obfuscation and Edge Protection.
- **Function:** Proxies traffic to the Portal (srv07). Bridges must use CDN-fronted domains to establish tunnels.
- **Protocols:** Enforced TLS (HTTPS) on port 443.

### B. HAProxy (The Gateway/Portal Edge)
- **Role:** Protocol Enforcement, SSL Termination, and Multi-Backend Routing.
- **Protocol Enforcement:** Redirects all port 80 (HTTP) traffic to port 443 (HTTPS).
- **Routing:** 
    - **SNI Routing:** Uses the `Host` header (e.g., `i-07.doctel.ir`) to route to specific Xray instances.
    - **Path Routing:** Uses URL paths (e.g., `/21-08-07-06`) to distinguish between multiple tunnels sharing the same domain/port.
- **Backends:** Forwards decrypted traffic to local Xray listening ports (e.g., 21075, 21076).

### C. Xray-core (The Tunneling Engine)
- **Role:** Managing the "Reverse-Reverse" Proxy tunnels.
- **Portal Mode (srv07):**
    - Listens on `127.0.0.1` (behind HAProxy).
    - Uses the `reverse` tag inside the `clients` (user) object to create a virtual outbound.
    - Traffic routed to this tag is "pushed" back through the established tunnel to the Bridge.
- **Bridge Mode (srv08/09):**
    - Initiates the connection to the Portal via the CDN.
    - Uses the **Simplified Reverse Proxy** syntax (V26+).
    - Traffic entering the `reverse-in` tag on the Bridge is routed `direct` to the Global Internet.

### D. Marzban (User & Node Management)
- **Role:** Admin UI and dynamic user management.
- **Deployment:** Containerized via Docker on srv07 and srv09.
- **Function:** Manages the VLESS users that Xray uses for tunneling.
- **Integration:** Communicates with Xray via the gRPC/REST API (e.g., ports 63053/60051). This allows adding/removing users without restarting Xray services.

### E. MySQL (Persistence Layer)
- **Role:** Centralized Database for Marzban.
- **Deployment:** Docker container on srv07.
- **Function:** Stores user data, traffic statistics, and node configurations. Replaces legacy file-based storage to ensure data integrity during orchestration.

### F. Technitium DNS (The Stealth Resolver)
- **Role:** Recursive DNS, DNS-over-HTTPS (DoH), and DNS-over-TLS (DoT) provider.
- **Deployment:** Docker container on srv07.
- **Function:** 
    - Prevents DNS hijacking by resolving queries over encrypted tunnels.
    - Provides advanced filtering and blocking to evade Iranian DPI/SNI detection.
    - Acts as the primary resolver for Xray and other containerized services.
- **Ports:** Typically listens on `53` (DNS), `5380` (Web UI), and `443/853` for DoH/DoT.

---

## 3. CRITICAL TECHNICAL STANDARDS (Xray v26+)

### I. The Simplified Outbound Syntax
In Xray v26, the reverse proxy configuration for VLESS is simplified. 
**Bridge-side VLESS outbounds MUST NOT use the `vnext` array** when using the simplified reverse proxy pattern. Instead, the `address`, `port`, and `users` are placed directly in the `settings` object.
*Failure to use this format results in "VLESS users: please use simplified outbound's config style to use reverse" errors.*

### II. Identity Matching
For a reverse tunnel to successfully register on the Portal:
1.  **Email Matching:** The `email` field in the Portal's `clients` object MUST exactly match the `email` field in the Bridge's outbound settings.
2.  **Seed Matching:** The `seed` field MUST be identical on both sides for stable session derivation.

### III. XHTTP Transport
- **XHTTP (SplitHTTP):** The preferred transport for CDN-proxied tunnels.
- **Mode:** Use `mode: "packet-up"` or `mode: "auto"` for general internet delivery.
- **Host Header:** Ensure the `host` field in `xhttpSettings` matches the domain fronted by the CDN.

### IV. HAProxy Performance
- **Splice:** Use `option splice-auto` for zero-copy data forwarding.
- **Buffering:** Use `no option http-buffer-request` to reduce latency for real-time traffic.
