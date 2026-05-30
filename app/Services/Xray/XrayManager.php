<?php

namespace App\Services\Xray;

use App\Models\Node;
use Exception;

class XrayManager
{
    protected XrayConfigRenderer $renderer;
    protected XrayValidator $validator;
    protected \App\Services\Safety\RiskGuard $riskGuard;
    protected array $connections = [];
    protected ?string $defaultConnection = null;

    public function __construct(
        XrayConfigRenderer $renderer, 
        XrayValidator $validator,
        \App\Services\Safety\RiskGuard $riskGuard
    ) {
        $this->renderer = $renderer;
        $this->validator = $validator;
        $this->riskGuard = $riskGuard;
    }

    /**
     * Get a specific Xray service connection.
     */
    public function connection($name = null): XrayService
    {
        // If $name is a Node model, we should validate it
        if ($name instanceof Node) {
            $this->riskGuard->validateNodeAccess($name);
            $host = $name->ip ?? $name->hostname;
            $port = $name->xray_grpc_port ?? 10085;
            $connName = "node-{$name->id}";
            
            if (!isset($this->connections[$connName])) {
                $this->connections[$connName] = new XrayService([
                    'host' => $host,
                    'port' => $port,
                ]);
            }
            return $this->connections[$connName];
        }

        $name = $name ?: $this->getDefaultDriver();

        if (!isset($this->connections[$name])) {
            $config = config("xray.connections.{$name}");
            if (!$config) {
                throw new Exception("Xray connection [{$name}] is not defined.");
            }
            $this->connections[$name] = new XrayService($config);
        }

        return $this->connections[$name];
    }

    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return $this->defaultConnection ?: config('xray.default');
    }

    /**
     * Set the default driver name.
     */
    public function setDefaultDriver(string $name): void
    {
        $this->defaultConnection = $name;
    }

    /**
     * Pass-through to default connection for convenience.
     */
    public function __call($method, $parameters)
    {
        return $this->connection()->$method(...$parameters);
    }

    public function generateConfig(Node $node): array
    {
        $config = $this->renderer->render($node);
        $this->riskGuard->validateConfig($config);
        return $config;
    }

    public function validateNode(Node $node): array
    {
        $config = $this->generateConfig($node);
        return $this->validator->validate($config);
    }

    public function mission(string $name)
    {
        $missions = [
            'portal' => \App\Services\Xray\Missions\PortalMission::class,
            'chain'  => \App\Services\Xray\Missions\ChainMission::class,
        ];

        if (!isset($missions[strtolower($name)])) {
            throw new \Exception("Mission {$name} not found.");
        }

        return new $missions[strtolower($name)]();
    }
}
