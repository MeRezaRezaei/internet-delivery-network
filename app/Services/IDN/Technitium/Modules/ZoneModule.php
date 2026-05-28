<?php

namespace App\Services\IDN\Technitium\Modules;

use App\Services\IDN\Technitium\TechnitiumClient;

class ZoneModule
{
    protected TechnitiumClient $client;

    public function __construct(TechnitiumClient $client)
    {
        $this->client = $client;
    }

    public function list(array $params = [])
    {
        return $this->client->request('zones/list', $params)->json('response.zones');
    }

    public function create(string $zone, string $type = 'Primary', array $options = [])
    {
        return $this->client->request('zones/create', array_merge([
            'zone' => $zone,
            'type' => $type,
        ], $options), 'POST')->json();
    }

    public function delete(string $zone)
    {
        return $this->client->request('zones/delete', ['zone' => $zone], 'POST')->json();
    }

    public function getOptions(string $zone)
    {
        return $this->client->request('zones/getOptions', ['zone' => $zone])->json('options');
    }
}
