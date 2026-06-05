# V2Ray Subscription Specification (v2rayN / v2rayNG)

This document describes the technical specifications for generating subscription links compatible with popular V2Ray clients like **v2rayN** (Windows) and **v2rayNG** (Android).

## 1. Subscription Container Format

A subscription link is a standard HTTP/HTTPS URL that returns a **Base64-encoded string**. 

- **Encoded Content:** A single Base64 string.
- **Decoded Content:** A list of server configuration URIs, with each URI on a **new line** (`\n`).

### Example (Decoded)
```text
vless://uuid@host:port?encryption=none&security=tls&type=ws#Remarks
vmess://eyAidiI6ICIyIiwgInBzIjogIlJlbWFya3MiLCAiYWRkIjogIkhvc3QiLCAicG9ydCI6IDQ0MywgImlkIjogIlVVSUQiLCAiYWlkIjogMCwgInNjeSI6ICJhdXRvIiwgIm5ldCI6ICJ3cyIsICJ0eXBlIjogIm5vbmUiLCAiaG9zdCI6ICJIb3N0IiwgInBhdGgiOiAiL3BhdGgiLCAidGxzIjogInRscyIsICJzbmkiOiAiSG9zdCIsICJmcCI6ICJjaHJvbWUiIH0=
trojan://password@host:port?security=tls&type=ws#Remarks
```

---

## 2. Protocol URI Formats

### 2.1 VLESS (`vless://`)
VLESS uses a URI-based format.

**Format:**
`vless://<uuid>@<address>:<port>?<query_params>#<remarks>`

**Common Query Parameters:**
| Parameter | Description |
| :--- | :--- |
| `encryption` | Usually `none`. |
| `security` | `tls`, `xtls`, or `reality`. |
| `type` | Transport network (`ws`, `grpc`, `tcp`, `httpupgrade`, `splithttp`, `xhttp`). |
| `sni` | Server Name Indication (for TLS). |
| `host` | Host header (for WS/HTTP). |
| `path` | Path (for WS/gRPC/HTTP). |
| `pbk` | Public Key (for REALITY). |
| `sid` | Short ID (for REALITY). |
| `fp` | TLS Fingerprint (e.g., `chrome`). |

### 2.2 VMess (`vmess://`)
VMess uses a Base64-encoded JSON object.

**Format:**
`vmess://<Base64(JSON)>`

**JSON Fields:**
```json
{
  "v": "2",
  "ps": "Remarks / Alias",
  "add": "Server IP or Domain",
  "port": 443,
  "id": "UUID",
  "aid": 0,
  "scy": "auto",
  "net": "ws",
  "type": "none",
  "host": "example.com",
  "path": "/v2ray",
  "tls": "tls",
  "sni": "example.com",
  "fp": "chrome"
}
```

### 2.3 Trojan (`trojan://`)
Trojan uses a URI-based format.

**Format:**
`trojan://<password>@<address>:<port>?<query_params>#<remarks>`

**Common Query Parameters:**
- `security`: `tls` or `xtls`.
- `sni`: Server Name Indication.
- `type`: Transport type.
- `host`: Host header.
- `path`: Path.

---

## 3. HTTP Response Headers

To improve user experience in clients, the following headers should be included in the subscription response:

| Header | Description | Example |
| :--- | :--- | :--- |
| `Content-Type` | Must be `text/plain`. | `text/plain; charset=utf-8` |
| `Profile-Title` | Sets the subscription name in the app. | `IDN Premium` |
| `Profile-Update-Interval` | Refresh interval in hours. | `24` |
| `Subscription-Userinfo` | Traffic and expiration data. | `upload=0; download=0; total=107374182400; expire=1749954800` |

---

## 4. Implementation Example (PHP/Laravel)

```php
public function getSubscription($uuid) {
    // 1. Fetch data from DB
    $subscription = Subscription::findOrFail($uuid);
    $nodes = $subscription->package->nodes;
    
    $uris = [];
    foreach ($nodes as $node) {
        // 2. Build Protocol URI
        $uri = "vless://{$subscription->user->uuid}@{$node->ip}:443?security=tls&type=ws#{$node->name}";
        $uris[] = $uri;
    }
    
    // 3. Combine and Base64 encode
    $content = base64_encode(implode("\n", $uris));
    
    // 4. Return with headers
    return response($content)
        ->header('Content-Type', 'text/plain')
        ->header('Profile-Title', 'My Service')
        ->header('Profile-Update-Interval', '12');
}
```
