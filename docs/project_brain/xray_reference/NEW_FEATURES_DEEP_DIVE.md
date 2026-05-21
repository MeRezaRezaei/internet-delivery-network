# Xray-core: New Features Deep Dive

This document analyzes the technical implementation of XHTTP, REALITY, and the Simplified Reverse Proxy.

## 1. XHTTP (SplitHTTP)
XHTTP (internally called `splithttp`) is a transport designed to split uplink and downlink traffic, potentially using different HTTP versions (H1, H2, H3) and obfuscation techniques.

### 1.1 Technical Mechanisms
*   **Traffic Splitting**: Unlike standard WebSockets or gRPC which use a single bi-directional stream, XHTTP can send data up via POST requests and receive data down via a long-lived response or SSE.
*   **Modes (`mode`)**:
    *   `packet-up`: Uplink data is sent as individual HTTP packets. High overhead but very stealthy.
    *   `stream-up`: Uplink data is sent as a single continuous stream in a POST body.
    *   `stream-one`: Both up and down use a single HTTP/2 or HTTP/3 stream.
*   **Multiplexing (`xmux`)**: XHTTP uses `xmux` to manage multiple logical connections over its split streams, optimizing throughput and reducing latency.
*   **Obfuscation (Padding)**:
    *   `xPaddingBytes`: Random padding added to requests to mask traffic patterns.
    *   `xPaddingObfsMode`: Advanced obfuscation that can make the traffic look like standard encrypted data.

## 2. REALITY
REALITY is a revolutionary stealth technology that eliminates the "server fingerprint" problem by proxying an existing, legitimate TLS handshake.

### 2.1 Handshake Flow
1.  **Client Hello**: The Xray client sends a TLS Client Hello that is a 100% match for a popular browser (using `utls`). It includes a special "auth key" hidden in the `session_id` or other extensions.
2.  **Server Decryption**: The REALITY server receives the handshake. It tries to decrypt the hidden auth key using its `private_key`.
3.  **Authentication**:
    *   If the key is valid, the server establishes a REALITY connection.
    *   If the key is INVALID, the server transparently forwards the connection to the `dest` (a legitimate website like `google.com` or `microsoft.com`).
4.  **Stealth**: To a firewall, the REALITY server behaves exactly like the target `dest`. It even returns the real certificates of the target site if the client is not an authorized REALITY client.

### 2.2 Advanced Cryptography
*   **X25519MLKEM768**: A hybrid post-quantum key exchange mechanism supported by REALITY.
*   **ML-DSA-65**: A post-quantum digital signature algorithm used for extra certificate verification.
*   **Short IDs**: A list of allowed IDs to prevent replay attacks and unauthorized scanning.

## 3. Simplified Reverse Proxy (VLESS)
The "Simplified" version of the reverse proxy is integrated directly into protocol workers, making it much easier to deploy than the traditional `app/reverse` standalone config.

### 3.1 Portal and Bridge
*   **Portal (Server Side)**: Waits for an incoming connection from the "Bridge". Once established, it can "call back" to the bridge to tunnel traffic from the Internet into the private network.
*   **Bridge (Client Side)**: Sited inside a private network. It initiates a connection to the Portal (usually via VLESS + Mux).
*   **Scaling Logic**:
    *   The Bridge monitors the number of active connections on its reverse tunnel.
    *   If the load exceeds a threshold (e.g., more than 16 connections per worker), it automatically initiates *another* Mux worker to the Portal to increase bandwidth.
    *   This "Simplified" logic removes the need for complex internal domain mapping in many cases.

### 3.2 Code Reference
*   `proxy/vless/inbound/inbound.go`: Implements the `Portal` logic.
*   `proxy/vless/outbound/outbound.go`: Implements the `Bridge` logic.
*   Uses `session.ContextWithIsReverseMux` to signal the core that a connection is a management tunnel.
