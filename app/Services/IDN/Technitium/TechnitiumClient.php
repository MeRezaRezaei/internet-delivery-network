<?php

namespace App\Services\IDN\Technitium;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

class TechnitiumClient
{
    protected string $baseUrl;
    protected ?string $token;
    protected string $username;
    protected string $password;

    public function __construct(string $baseUrl, ?string $token = null, string $username = 'admin', string $password = 'password')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->token = $token;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Authenticate and get a session token.
     */
    public function login(): string
    {
        $response = Http::get("{$this->baseUrl}/api/user/login", [
            'user' => $this->username,
            'pass' => $this->password,
        ]);

        if ($response->successful() && $response->json('status') === 'ok') {
            $this->token = $response->json('token');
            return $this->token;
        }

        throw new \Exception("Technitium login failed: " . $response->body());
    }

    /**
     * Send an API request.
     */
    public function request(string $endpoint, array $params = [], string $method = 'GET'): Response
    {
        if (!$this->token) {
            $this->login();
        }

        $params['token'] = $this->token;

        $url = "{$this->baseUrl}/api/{$endpoint}";

        $response = $method === 'POST' 
            ? Http::asForm()->post($url, $params) 
            : Http::get($url, $params);

        if ($response->json('status') === 'error') {
            Log::error("Technitium API Error: " . $response->json('errorMessage'), [
                'endpoint' => $endpoint,
                'params' => $params,
                'response' => $response->json(),
            ]);
            throw new \Exception("Technitium API Error: " . $response->json('errorMessage'));
        }

        return $response;
    }

    public function zones(): Modules\ZoneModule
    {
        return new Modules\ZoneModule($this);
    }

    public function records(): Modules\RecordModule
    {
        return new Modules\RecordModule($this);
    }

    public function settings(): Modules\SettingsModule
    {
        return new Modules\SettingsModule($this);
    }

    public function cluster(): Modules\ClusterModule
    {
        return new Modules\ClusterModule($this);
    }

    public function user(): Modules\UserModule
    {
        return new Modules\UserModule($this);
    }
}
