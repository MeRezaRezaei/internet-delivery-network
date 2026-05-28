# Private Memory

This file contains private notes, credentials, and local setup details.

## Tailscale API Credentials

To use the Tailscale API integration, you need to provide OAuth credentials in your `.env` file.

1.  **Generate OAuth Client:**
    - Go to the [Tailscale Admin Console](https://login.tailscale.com/admin/settings/oauth).
    - Create a new OAuth client.
    - Scopes recommended: `devices:read`, `keys:write`, `acl:read`, `acl:write`.
2.  **Add to `.env`:**
    ```env
    TAILSCALE_CLIENT_ID=your_client_id
    TAILSCALE_CLIENT_SECRET=your_client_secret
    TAILSCALE_TAILNET=your_tailnet_name (e.g., example.com)
    ```

## Xray gRPC API
- Port: 10085 (default)
- Multi-node management is enabled via `XrayServiceProvider`.
