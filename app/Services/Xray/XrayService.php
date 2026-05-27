<?php

namespace App\Services\Xray;

use Grpc\ChannelCredentials;
use Xray\App\Proxyman\Command\HandlerServiceClient;
use Xray\App\Stats\Command\StatsServiceClient;
use Xray\App\Stats\Command\QueryStatsRequest;
use Xray\App\Stats\Command\SysStatsRequest;
use Xray\App\Proxyman\Command\RemoveInboundRequest;
use Xray\App\Proxyman\Command\AddInboundRequest;
use Xray\App\Proxyman\Command\ListInboundsRequest;
use Xray\App\Proxyman\InboundHandlerConfig;
use Exception;

class XrayService
{
    protected array $config;
    protected ?HandlerServiceClient $handler = null;
    protected ?StatsServiceClient $stats = null;
    
    // Default timeout for gRPC calls in microseconds (5 seconds)
    protected const DEFAULT_TIMEOUT = 5000000; 

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    protected function getOptions(): array
    {
        return [
            'credentials' => ChannelCredentials::createInsecure(),
            'grpc.timeout' => self::DEFAULT_TIMEOUT,
        ];
    }

    public function handler(): HandlerServiceClient
    {
        if (!$this->handler) {
            $hostname = "{$this->config['host']}:{$this->config['port']}";
            $this->handler = new HandlerServiceClient($hostname, $this->getOptions());
        }
        return $this->handler;
    }

    public function stats(): StatsServiceClient
    {
        if (!$this->stats) {
            $hostname = "{$this->config['host']}:{$this->config['port']}";
            $this->stats = new StatsServiceClient($hostname, $this->getOptions());
        }
        return $this->stats;
    }

    // --- High-level API methods with Validation ---

    public function getSysStats(): array
    {
        $request = new SysStatsRequest();
        list($response, $status) = $this->stats()->SysStats($request)->wait();
        $this->ensureSuccess($status, "SysStats");
        
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

    public function queryStats(string $pattern = "", bool $reset = false): array
    {
        $request = new QueryStatsRequest();
        $request->setPattern($pattern);
        $request->setReset($reset);

        list($response, $status) = $this->stats()->QueryStats($request)->wait();
        $this->ensureSuccess($status, "QueryStats");

        $stats = [];
        if ($response) {
            foreach ($response->getStat() as $stat) {
                $stats[$stat->getName()] = $stat->getValue();
            }
        }
        return $stats;
    }

    public function addInbound(InboundHandlerConfig $inbound): bool
    {
        $tag = $inbound->getTag();
        $request = new AddInboundRequest();
        $request->setInbound($inbound);

        list($response, $status) = $this->handler()->AddInbound($request)->wait();
        $this->ensureSuccess($status, "AddInbound [{$tag}]");

        // --- Post-Apply Verification ---
        if (!$this->verifyInboundActive($tag)) {
            throw new Exception("AddInbound success reported by gRPC, but inbound [{$tag}] is not active in the core. Check OS port bindings.");
        }

        return true;
    }

    public function removeInbound(string $tag): bool
    {
        $request = new RemoveInboundRequest();
        $request->setTag($tag);

        list($response, $status) = $this->handler()->RemoveInbound($request)->wait();
        $this->ensureSuccess($status, "RemoveInbound [{$tag}]");

        return true;
    }

    /**
     * Verify if an inbound tag actually exists in the running core.
     */
    public function verifyInboundActive(string $tag): bool
    {
        $request = new ListInboundsRequest();
        list($response, $status) = $this->handler()->ListInbounds($request)->wait();
        
        if ($status->code !== 0) return false;

        foreach ($response->getInbound() as $inbound) {
            if ($inbound->getTag() === $tag) return true;
        }

        return false;
    }

    public function ping(): bool
    {
        try {
            $this->getSysStats();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function ensureSuccess($status, string $context): void
    {
        if ($status->code !== 0) {
            // Map common gRPC codes to helpful exceptions
            $msg = "Xray gRPC Error in {$context} [Code {$status->code}]: {$status->details}";
            
            if ($status->code === 14) {
                $msg = "Xray Node Unreachable: {$this->config['host']}:{$this->config['port']} is down or gRPC is disabled.";
            } elseif ($status->code === 4) {
                $msg = "Xray gRPC Timeout: Node took too long to respond to {$context}.";
            }

            throw new Exception($msg);
        }
    }
}
