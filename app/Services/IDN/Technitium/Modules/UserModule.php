<?php

namespace App\Services\IDN\Technitium\Modules;

use App\Services\IDN\Technitium\TechnitiumClient;

class UserModule
{
    protected TechnitiumClient $client;

    public function __construct(TechnitiumClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get SSO/OIDC status and configuration.
     */
    public function ssoStatus()
    {
        return $this->client->request('user/sso/status')->json('response');
    }

    /**
     * Authenticate and get a session token.
     */
    public function login(string $username, string $password)
    {
        return $this->client->request('user/login', [
            'user' => $username,
            'pass' => $password,
        ])->json();
    }
}
