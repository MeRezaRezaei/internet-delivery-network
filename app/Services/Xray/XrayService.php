<?php

namespace App\Services\Xray;

use Grpc\ChannelCredentials;
use Xray\App\Proxyman\Command\HandlerServiceClient;
use Xray\App\Stats\Command\StatsServiceClient;
use Xray\App\Stats\Command\GetStatsRequest;
use Xray\App\Stats\Command\QueryStatsRequest;
use Xray\App\Proxyman\Command\AddInboundRequest;
use Xray\App\Proxyman\Command\RemoveInboundRequest;
use Xray\App\Proxyman\Command\AddOutboundRequest;
use Xray\App\Proxyman\Command\RemoveOutboundRequest;
use Xray\Core\InboundHandlerConfig;
use Xray\Core\OutboundHandlerConfig;

class XrayService
{
    protected $config;
    protected $handlerClient;
    protected $statsClient;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get the HandlerServiceClient instance.
     *
     * @return HandlerServiceClient
     */
    public function handler()
    {
        if (!$this->handlerClient) {
            $this->handlerClient = new HandlerServiceClient(
                "{$this->config['host']}:{$this->config['port']}",
                [
                    'credentials' => $this->getCredentials(),
                ]
            );
        }
        return $this->handlerClient;
    }

    /**
     * Get the StatsServiceClient instance.
     *
     * @return StatsServiceClient
     */
    public function stats()
    {
        if (!$this->statsClient) {
            $this->statsClient = new StatsServiceClient(
                "{$this->config['host']}:{$this->config['port']}",
                [
                    'credentials' => $this->getCredentials(),
                ]
            );
        }
        return $this->statsClient;
    }

    protected function getCredentials()
    {
        if ($this->config['secure']) {
            return ChannelCredentials::createSsl();
        }
        return ChannelCredentials::createInsecure();
    }

    // --- Helper Methods ---

    /**
     * Get stats for a specific user/inbound.
     *
     * @param string $name
     * @param bool $reset
     * @return \Xray\App\Stats\Command\Stat|null
     */
    public function getStat(string $name, bool $reset = false)
    {
        $request = new GetStatsRequest();
        $request->setName($name);
        $request->setReset($reset);

        list($response, $status) = $this->stats()->GetStats($request)->wait();

        if ($status->code !== 0) {
            return null;
        }

        return $response->getStat();
    }

    /**
     * Query stats with a pattern.
     *
     * @param string $pattern
     * @param bool $reset
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function queryStats(string $pattern = "", bool $reset = false)
    {
        $request = new QueryStatsRequest();
        $request->setPattern($pattern);
        $request->setReset($reset);

        list($response, $status) = $this->stats()->QueryStats($request)->wait();

        if ($status->code !== 0) {
            return [];
        }

        return $response->getStat();
    }

    /**
     * Add an inbound handler.
     *
     * @param \Xray\Core\InboundHandlerConfig $inbound
     * @return bool
     */
    public function addInbound($inbound)
    {
        $request = new AddInboundRequest();
        $request->setInbound($inbound);

        list($response, $status) = $this->handler()->AddInbound($request)->wait();

        return $status->code === 0;
    }

    /**
     * Remove an inbound handler by tag.
     *
     * @param string $tag
     * @return bool
     */
    public function removeInbound(string $tag)
    {
        $request = new RemoveInboundRequest();
        $request->setTag($tag);

        list($response, $status) = $this->handler()->RemoveInbound($request)->wait();

        return $status->code === 0;
    }
}
