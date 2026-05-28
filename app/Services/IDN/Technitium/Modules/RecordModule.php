<?php

namespace App\Services\IDN\Technitium\Modules;

use App\Services\IDN\Technitium\TechnitiumClient;

class RecordModule
{
    protected TechnitiumClient $client;

    public function __construct(TechnitiumClient $client)
    {
        $this->client = $client;
    }

    public function get(string $zone, ?string $domain = null, ?string $type = null)
    {
        $params = ['zone' => $zone];
        if ($domain) $params['domain'] = $domain;
        if ($type) $params['type'] = $type;

        return $this->client->request('zones/records/get', $params)->json('response.records');
    }

    public function add(string $zone, string $domain, string $type, string $value, int $ttl = 3600, array $options = [])
    {
        $params = [
            'zone' => $zone,
            'domain' => $domain,
            'type' => $type,
            'ttl' => $ttl,
        ];

        // Map the primary value to the correct parameter
        $params[$this->getValueKey($type)] = $value;

        return $this->client->request('zones/records/add', array_merge($params, $options), 'POST')->json();
    }

    public function update(string $zone, string $domain, string $type, string $newValue, string $oldValue, int $ttl = 3600, array $options = [])
    {
        return $this->client->request('zones/records/update', array_merge([
            'zone' => $zone,
            'domain' => $domain,
            'type' => $type,
            'value' => $oldValue,
            'newValue' => $newValue,
            'ttl' => $ttl,
        ], $options), 'POST')->json();
    }

    public function delete(string $zone, string $domain, string $type, ?string $value = null)
    {
        $params = [
            'zone' => $zone,
            'domain' => $domain,
            'type' => $type,
        ];
        
        if ($value) {
            $params[$this->getValueKey($type)] = $value;
        }

        return $this->client->request('zones/records/delete', $params, 'POST')->json();
    }

    protected function getValueKey(string $type): string
    {
        return match (strtoupper($type)) {
            'A' => 'ipAddress',
            'AAAA' => 'ipAddress',
            'CNAME' => 'cname',
            'MX' => 'exchange',
            'TXT' => 'text',
            'NS' => 'nameServer',
            'PTR' => 'ptr',
            'SRV' => 'target',
            default => 'value',
        };
    }
}
