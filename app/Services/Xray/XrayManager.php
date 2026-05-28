<?php

namespace App\Services\Xray;

use App\Models\Node;

class XrayManager
{
    protected XrayConfigRenderer $renderer;
    protected XrayValidator $validator;

    public function __construct(XrayConfigRenderer $renderer, XrayValidator $validator)
    {
        $this->renderer = $renderer;
        $this->validator = $validator;
    }

    public function generateConfig(Node $node): array
    {
        return $this->renderer->render($node);
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
        ];

        if (!isset($missions[strtolower($name)])) {
            throw new \Exception("Mission {$name} not found.");
        }

        return new $missions[strtolower($name)]();
    }
}
