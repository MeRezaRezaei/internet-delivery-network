<?php

namespace App\Services\Tailscale;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use Exception;

class TailscaleService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $tailnet;
    protected string $baseUrl;

    public function __construct()
    {
        $this->clientId = config('tailscale.client_id', '');
        $this->clientSecret = config('tailscale.client_secret', '');
        $this->tailnet = config('tailscale.tailnet', '');
        $this->baseUrl = config('tailscale.base_url', 'https://api.tailscale.com/api/v2/');
    }

    /**
     * Get a pre-configured HTTP client instance.
     */
    protected function client()
    {
        return Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->baseUrl($this->baseUrl)
            ->acceptJson();
    }

    /**
     * List all devices in the tailnet.
     */
    public function devices(): array
    {
        $response = $this->client()->get("tailnet/{$this->tailnet}/devices");

        if ($response->failed()) {
            $this->handleError($response);
        }

        return $response->json('devices') ?? [];
    }

    /**
     * Get details for a specific device.
     */
    public function device(string $id): array
    {
        $response = $this->client()->get("device/{$id}");

        if ($response->failed()) {
            $this->handleError($response);
        }

        return $response->json() ?? [];
    }

    /**
     * Generate a new authentication key.
     *
     * @param array $capabilities Capabilities for the key (e.g., devices create tags)
     * @param int $expirySeconds Expiry time in seconds (default 90 days)
     */
    public function createAuthKey(array $capabilities = [], int $expirySeconds = 7776000): array
    {
        $data = [
            'capabilities' => $capabilities,
            'expirySeconds' => $expirySeconds,
        ];

        $response = $this->client()->post("tailnet/{$this->tailnet}/keys", $data);

        if ($response->failed()) {
            $this->handleError($response);
        }

        return $response->json() ?? [];
    }

    /**
     * Retrieve the current ACL policy.
     */
    public function acl(): array
    {
        $response = $this->client()->get("tailnet/{$this->tailnet}/acl");

        if ($response->failed()) {
            $this->handleError($response);
        }

        return $response->json() ?? [];
    }

    /**
     * Update the ACL policy.
     */
    public function updateAcl(array $acl): array
    {
        $response = $this->client()->post("tailnet/{$this->tailnet}/acl", $acl);

        if ($response->failed()) {
            $this->handleError($response);
        }

        return $response->json() ?? [];
    }

    /**
     * Handle API errors.
     *
     * @throws Exception
     */
    protected function handleError(Response $response): void
    {
        $error = $response->json('message') ?? $response->body();
        Log::error("Tailscale API Error: {$response->status()} - {$error}");
        
        throw new Exception("Tailscale API request failed: {$error}", $response->status());
    }
}
