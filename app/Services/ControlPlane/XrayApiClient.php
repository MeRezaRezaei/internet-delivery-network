<?php

namespace App\Services\ControlPlane;

use App\Services\Xray\XrayService;
use Xray\App\Proxyman\Command\AddInboundRequest;
use Xray\App\Proxyman\Command\RemoveInboundRequest;
use Xray\App\Proxyman\Command\AddOutboundRequest;
use Xray\App\Proxyman\Command\RemoveOutboundRequest;
use Xray\App\Proxyman\InboundHandlerConfig;
use Xray\App\Stats\Command\QueryStatsRequest;
use Xray\App\Stats\Command\GetStatsRequest;
use Xray\App\Stats\Command\SysStatsRequest;
use Xray\Common\Serial\TypedMessage;
use Exception;

class XrayApiClient
{
    protected XrayService $service;

    public function __construct(XrayService $service)
    {
        $this->service = $service;
    }

    /**
     * Get system statistics.
     */
    public function getSysStats(): array
    {
        $request = new SysStatsRequest();
        list($response, $status) = $this->service->stats()->SysStats($request)->wait();
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
     * Query all available statistics.
     */
    public function queryStats(string $pattern = "", bool $reset = false): array
    {
        $request = new QueryStatsRequest();
        $request->setPattern($pattern);
        $request->setReset($reset);

        list($response, $status) = $this->service->stats()->QueryStats($request)->wait();
        $this->ensureSuccess($status);

        $stats = [];
        foreach ($response->getStat() as $stat) {
            $stats[$stat->getName()] = $stat->getValue();
        }
        return $stats;
    }

    /**
     * Add a new inbound dynamically.
     * 
     * @param string $jsonConfig Raw JSON configuration for the inbound.
     */
    public function addInbound(string $jsonConfig): bool
    {
        // This requires parsing JSON into Protobuf objects.
        // Xray gRPC expects an InboundHandlerConfig.
        // In a real implementation, we would use a JSON-to-Protobuf converter.
        // For now, we assume the caller provides a pre-constructed object or we handle basic types.
        
        throw new Exception("Dynamic JSON-to-Protobuf conversion for AddInbound not yet fully implemented. Use typed methods.");
    }

    /**
     * Remove an inbound by tag.
     */
    public function removeInbound(string $tag): bool
    {
        $request = new RemoveInboundRequest();
        $request->setTag($tag);

        list($response, $status) = $this->service->handler()->RemoveInbound($request)->wait();
        $this->ensureSuccess($status);
        
        return true;
    }

    /**
     * List all active inbounds.
     */
    public function listInbounds(): array
    {
        // Note: Xray v26 HandlerService might not have ListInbounds in all versions.
        // We'll use the generated one if available.
        if (!method_exists($this->service->handler(), 'ListInbounds')) {
            throw new Exception("ListInbounds method not found in HandlerServiceClient stub.");
        }

        $request = new \Xray\App\Proxyman\Command\ListInboundsRequest();
        list($response, $status) = $this->service->handler()->ListInbounds($request)->wait();
        $this->ensureSuccess($status);

        return $response->getInbound();
    }

    /**
     * Ensure the gRPC call was successful.
     */
    protected function ensureSuccess($status): void
    {
        if ($status->code !== 0) {
            throw new Exception("gRPC Error [{$status->code}]: {$status->details}");
        }
    }
}
