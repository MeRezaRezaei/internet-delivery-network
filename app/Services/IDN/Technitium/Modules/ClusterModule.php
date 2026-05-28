<?php

namespace App\Services\IDN\Technitium\Modules;

use App\Services\IDN\Technitium\TechnitiumClient;

class ClusterModule
{
    protected TechnitiumClient $client;

    public function __construct(TechnitiumClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get details about the cluster nodes and status.
     */
    public function info()
    {
        return $this->client->request('cluster/info')->json('response');
    }

    /**
     * Get dashboard stats for the entire cluster or a specific node.
     * @param string $node 'cluster' for aggregate data, or the node domain name.
     */
    public function stats(string $node = 'cluster')
    {
        return $this->client->request('stats/dashboard', ['node' => $node])->json('response');
    }
}
