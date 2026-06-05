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
*   **Portal (Server Side / Inside Iran)**: Acts as the passive listener. It waits for an incoming connection from the Bridge. 
*   **Bridge (Client Side / Outside US/DE)**: Acts as the active initiator. It connects to the Portal automatically via VLESS outbound configurations to establish and maintain the reverse tunnels.
*   **Scaling Logic**:
    *   The Bridge monitors active connections. If load limits are reached, it dynamically opens additional tunnels/Mux channels to scale bandwidth.

### 3.2 Directional Tag Registration & Routing Rules
Configuring the `reverse` block inside different protocol handlers changes its nature entirely, defining whether it acts as a virtual inbound or outbound.

#### Portal Side (VLESS Inbound)
When `reverse` is placed inside the client object of a VLESS inbound (Portal side):
```json
"settings": {
  "clients": [
    {
      "id": "YOUR_UUID",
      "email": "channel@reverse",
      "reverse": { "tag": "reverse-out-tag" }
    }
  ]
}
```
*   **Registration**: Registers a virtual **OUTBOUND** with the tag `"reverse-out-tag"`.
*   **Routing Rule**: Since it is a virtual outbound, you route incoming SOCKS/User traffic **TO** it using:
    ```json
    { "type": "field", "inboundTag": ["socks-in"], "outboundTag": "reverse-out-tag" }
    ```
*   **Pitfall**: Never match `"reverse-out-tag"` as an `"inboundTag"` in Portal routing rules. Since it is a virtual outbound, it never receives incoming requests on the Portal side.

#### Bridge Side (VLESS Outbound)
When `reverse` is placed inside the settings of a VLESS outbound (Bridge side):
```json
"settings": {
  "address": "portal.domain.com",
  "port": 443,
  "id": "YOUR_UUID",
  "reverse": { "tag": "reverse-in-tag" }
}
```
*   **Registration**: Registers a virtual **INBOUND** with the tag `"reverse-in-tag"`.
*   **Routing Rule**: Since it is a virtual inbound, egress traffic exiting the tunnel enters Xray's routing system under this tag. You must route it **FROM** this tag to the internet using:
    ```json
    { "type": "field", "inboundTag": ["reverse-in-tag"], "outboundTag": "direct" }
    ```

### 3.3 Code Reference
*   `proxy/vless/inbound/inbound.go`: Implements the `Portal` logic.
*   `proxy/vless/outbound/outbound.go`: Implements the `Bridge` logic.
*   Uses `session.ContextWithIsReverseMux` to signal the core that a connection is a management tunnel.

