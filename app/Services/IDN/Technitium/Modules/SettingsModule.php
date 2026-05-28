<?php

namespace App\Services\IDN\Technitium\Modules;

use App\Services\IDN\Technitium\TechnitiumClient;

class SettingsModule
{
    protected TechnitiumClient $client;

    public function __construct(TechnitiumClient $client)
    {
        $this->client = $client;
    }

    public function get()
    {
        return $this->client->request('settings/get')->json('response.settings');
    }

    public function set(array $settings)
    {
        return $this->client->request('settings/set', $settings, 'POST')->json();
    }
}
