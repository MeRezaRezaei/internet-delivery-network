# XHTTP Obfuscation & Padding Strategy

This document outlines the design and theoretical mechanisms of Xray's native **SplitHTTP (XHTTP) Obfuscation & Padding** engine, and documents the configuration parameters required to prevent DPI and GFW fingerprinting.

---

## 1. What is XHTTP Obfuscation & Padding?

GFW and middleboxes inspect TLS connections by performing statistical analysis on **packet sizes** and **packet timing (intervals)**. Even inside an encrypted TLS stream, the sequence of packet lengths (e.g., a SOCKS handshake followed by HTTP requests) creates a recognizable signature.

XHTTP (SplitHTTP) actively defeats this using native padding and obfuscation blocks inside `xhttpSettings`:
*   `xPaddingBytes`: Adds random bytes of padding to the HTTP requests, changing the packet size distribution completely.
*   `xPaddingObfsMode`: Advanced obfuscation that makes the padding packets mimic standard, legitimate encrypted web payloads (rather than trailing null bytes).

---

## 2. Advanced Configuration Parameters

These parameters reside in the `xhttpSettings` block in your `streamSettings`:

```json
"xhttpSettings": {
    "path": "/xhttp-path",
    "mode": "stream-up",
    "extra": {
        "xPaddingBytes": "100-1000",
        "xPaddingObfsMode": true
    }
}
```

### 2.1 `xPaddingBytes` *(Default: disabled)*
*   **Syntax**: Accepts a range string representing the minimum and maximum padding bytes, e.g., `"100-1000"`.
*   **Behavior**: For every fragmented HTTP packet sent over the reverse channel, XHTTP generates a random integer within this range and appends that many random bytes to the request payload.
*   **DPI Evasion**: This destroys GFW's packet length analysis. Two identical user requests will have completely different packet sizes on the Wire.

### 2.2 `xPaddingObfsMode` *(Default: false)*
*   **Syntax**: Boolean (`true` / `false`).
*   **Behavior**: When enabled, the added padding is obfuscated using randomized, non-zero entropy patterns that look exactly like standard AES-encrypted app data or browser assets. This prevents firewalls from identifying the padding by scanning for null byte streams.

---

## 3. High-Security Obfuscation Evasion Checklist

To ensure it is statistically impossible for the GFW to identify the connection, we maximize padding and minimize static footprints:

1.  **High Range Padding**: Set `"xPaddingBytes": "500-1500"`. This forces substantial, highly variable packet sizes that mimic large image downloads or heavy CSS/JS assets.
2.  **Obfuscation Enabled**: Set `"xPaddingObfsMode": true` to eliminate all zero-entropy signatures.
3.  **Masquerade Mode (`stream-up`)**: Massages the uplink to look like standard, continuous gRPC data streams rather than short chunks.
