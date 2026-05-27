<?php

namespace App\Services\Xray;

use Grpc\ChannelCredentials;
use Xray\App\Proxyman\Command\HandlerServiceClient;
use Xray\App\Stats\Command\StatsServiceClient;
use Xray\App\Stats\Command\QueryStatsRequest;
use Xray\App\Stats\Command\SysStatsRequest;
use Xray\App\Proxyman\Command\RemoveInboundRequest;
use Xray\App\Proxyman\Command\AddInboundRequest;
use Xray\App\Proxyman\InboundHandlerConfig;
use Exception;

class XrayService
{
    protected array $config;
    protected ?HandlerServiceClient $handler = null;
    protected ?StatsServiceClient $stats = null;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get the HandlerService client.
     */
    public function handler(): HandlerServiceClient
    {
        if (!$this->handler) {
            $hostname = "{$this->config['host']}:{$this->config['port']}";
            $this->handler = new HandlerServiceClient($hostname, [
                'credentials' => ChannelCredentials::createInsecure(),
            ]);
        }
        return $this->handler;
    }

    /**
     * Get the StatsService client.
     */
    public function stats(): StatsServiceClient
    {
        if (!$this->stats) {
            $hostname = "{$this->config['host']}:{$this->config['port']}";
            $this->stats = new StatsServiceClient($hostname, [
                'credentials' => ChannelCredentials::createInsecure(),
            ]);
        }
        return $this->stats;
    }

    // --- High-level API methods ---

    /**
     * Get system statistics.
     */
    public function getSysStats(): array
    {
        $request = new SysStatsRequest();
        list($response, $status) = $this->stats()->SysStats($request)->wait();
        $this->ensureSuccess($status);
        
        return [
            'uptime' => $response->getUptime(),
            'num_goroutine' => $response->getNumGoroutine(),
            'alloc' => $response->getAlloc(),
            'total_alloc' => $response->getTotalAlloc(),
            'sys' => $response->getSys(),
            'mallocs' => $response->getMallocs(),
            'frees' => $response->getFrees(),
            'live_objects' => $response->getLiveObjects(),
            'num_gc' => $response->getNumGC(),
            'pause_total_ns' => $response->getPauseTotalNs(),
        ];
    }

    /**
     * Query stats by pattern.
     */
    public function queryStats(string $pattern = "", bool $reset = false): array
    {
        $request = new QueryStatsRequest();
        $request->setPattern($pattern);
        $request->setReset($reset);

        list($response, $status) = $this->stats()->QueryStats($request)->wait();
        $this->ensureSuccess($status);

        $stats = [];
        if ($response) {
            foreach ($response->getStat() as $stat) {
                $stats[$stat->getName()] = $stat->getValue();
            }
        }
        return $stats;
    }

    /**
     * Remove an inbound.
     */
    public function removeInbound(string $tag): bool
    {
        $request = new RemoveInboundRequest();
        $request->setTag($tag);

        list($response, $status) = $this->handler()->RemoveInbound($request)->wait();
        $this->ensureSuccess($status);

        return true;
    }

    /**
     * Add an inbound (Native Protobuf Object).
     */
    public function addInbound(InboundHandlerConfig $inbound): bool
    {
        $request = new AddInboundRequest();
        $request->setInbound($inbound);

        list($response, $status) = $this->handler()->AddInbound($request)->wait();
        $this->ensureSuccess($status);

        return true;
    }

    /**
     * Validate the connection.
     */
    public function ping(): bool
    {
        try {
            $this->getSysStats();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Ensure gRPC call success.
     */
    protected function ensureSuccess($status): void
    {
        if ($status->code !== 0) {
            throw new Exception("Xray gRPC Error [{$status->code}]: {$status->details}");
        }
    }
}
