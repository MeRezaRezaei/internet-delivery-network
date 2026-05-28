# Technitium DNS Integration Guide

This document provides a comprehensive overview of the Technitium DNS Server integration implemented in the IDN project.

## 1. SDK Overview
The SDK is located in `app/Services/IDN/Technitium/` and provides a modular interface to the Technitium HTTP API.

### Core Components
- **`TechnitiumClient`**: Handles authentication and base API requests.
- **`Technitium` Facade**: Provides a clean entry point (`App\Facades\Technitium`).
- **Modules**:
    - `ZoneModule`: Management of DNS zones.
    - `RecordModule`: CRUD for DNS records with automatic parameter mapping (A, CNAME, MX, etc.).
    - `ClusterModule`: Support for multi-node clustering and aggregate statistics.
    - `UserModule`: SSO/OIDC status checks and session management.

## 2. Configuration
Configuration is managed via `config/technitium.php` and environment variables:
```env
TECHNITIUM_URL=http://localhost:5380
TECHNITIUM_USERNAME=admin
TECHNITIUM_PASSWORD=password
TECHNITIUM_TOKEN=optional_api_token
```

## 3. Testing & Verification
### Docker Environment
Technitium is integrated into the `docker-compose.yml`. For verification, the container includes:
- `tshark` (Wireshark engine)
- `tcpdump`
- `dig`

### DNSVerifier Utility
Located at `app/Utils/DNSVerifier.php`, this utility provides:
- **Protocol Analysis**: Uses `tshark` to capture and analyze DNS packets in JSON format.
- **Resolution Checks**: High-level verification of DNS record propagation.

## 4. Usage Examples
### Adding an A Record
```php
use App\Facades\Technitium;

Technitium::records()->add(
    zone: 'example.com',
    domain: 'vpn.example.com',
    type: 'A',
    value: '1.2.3.4'
);
```

### Checking Cluster Stats
```php
$stats = Technitium::cluster()->stats('cluster');
```

## 5. Merging & Integration Notes
- **Branch**: `feat/technitium-sdk`
- **Dependencies**: Requires `php-curl` and `docker-compose` for the testing environment.
- **Security**: Always use HTTPS (`port 53443`) in production and use dedicated API tokens instead of admin passwords where possible.
