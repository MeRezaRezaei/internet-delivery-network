<?php

namespace App\Services\ControlPlane;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class TechnitiumService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('technitium.api_key', '');
        $this->baseUrl = (string) config('technitium.base_url', 'http://idn-technitium:5380/api/');
    }

    protected function client()
    {
        return Http::baseUrl($this->baseUrl)
            ->withQueryParameters(['token' => $this->apiKey])
            ->acceptJson();
    }

    /**
     * Set DNS blocklist (ad-blocking / filtering).
     */
    public function setBlocklist(bool $enabled): bool
    {
        $response = $this->client()->get('settings/set', [
            'blocklist' => $enabled ? 'true' : 'false'
        ]);

        if ($response->failed()) {
            $this->handleError($response);
        }

        return $response->json('status') === 'ok';
    }

    /**
     * Add or update a DNS record.
     */
    public function updateRecord(string $domain, string $type, string $value): bool
    {
        $response = $this->client()->get('zones/records/add', [
            'domain' => $domain,
            'type' => $type,
            'value' => $value,
            'overwrite' => 'true'
        ]);

        if ($response->failed()) {
            $this->handleError($response);
        }

        return $response->json('status') === 'ok';
    }

    protected function handleError($response): void
    {
        $error = $response->json('error') ?? $response->body();
        Log::error("Technitium API Error: {$response->status()} - {$error}");
        throw new Exception("Technitium API request failed: {$error}");
    }
}
