<?php

namespace App\Services\Xray;

use App\Models\Node;
use App\Models\XrayInbound;
use App\Models\XrayOutbound;

class XrayConfigRenderer
{
    public function render(Node $node): array
    {
        $node->load([
            'ports.inbound.sniffing',
            'ports.inbound.policyLevel',
            'ports.inbound.vless.clients.client',
            'ports.inbound.xhttp',
            'ports.inbound.grpc',
            'ports.inbound.tls',
            'ports.inbound.reality',
            'ports.inbound.fallbacks',
            'outbounds.vless.clients.client',
            'outbounds.xhttp',
            'outbounds.grpc',
            'outbounds.tls',
            'outbounds.reality',
            'balancers',
            'routingRules',
            'policyLevels',
        ]);

        return [
            'log' => [
                'loglevel' => 'debug',
                'access' => '/var/log/xray/access.log',
                'error' => '/var/log/xray/error.log',
            ],
            'api' => [
                'tag' => 'api',
                'services' => ['HandlerService', 'StatsService'],
            ],
            'stats' => (object)[],
            'policy' => $this->renderPolicy($node),
            'inbounds' => $this->renderInbounds($node),
            'outbounds' => $this->renderOutbounds($node),
            'routing' => $this->renderRouting($node),
        ];
    }

    protected function renderPolicy(Node $node): array
    {
        $levels = [];
        foreach ($node->policyLevels as $policy) {
            $levels[(string)$policy->level_id] = [
                'handshake' => $policy->handshake,
                'connIdle' => $policy->conn_idle,
                'uplinkOnly' => 2,
                'downlinkOnly' => 5,
                'bufferSize' => $policy->buffer_size,
            ];
        }

        return [
            'levels' => (object)$levels,
            'system' => [
                'statsInboundUplink' => true,
                'statsInboundDownlink' => true,
                'statsOutboundUplink' => true,
                'statsOutboundDownlink' => true,
            ],
        ];
    }

    protected function renderInbounds(Node $node): array
    {
        $inbounds = [];
        
        // Always add API inbound
        $inbounds[] = [
            'listen' => '127.0.0.1',
            'port' => 10085,
            'protocol' => 'dokodemo-door',
            'settings' => [
                'address' => '127.0.0.1',
            ],
            'tag' => 'api',
        ];

        foreach ($node->ports as $port) {
            if (!$port->inbound) continue;
            
            $inbound = $port->inbound;
            $config = [
                'port' => $port->port_number,
                'listen' => '0.0.0.0',
                'tag' => $inbound->tag,
            ];

            if ($inbound->sniffing) {
                $config['sniffing'] = [
                    'enabled' => $inbound->sniffing->enabled,
                    'destOverride' => explode(',', $inbound->sniffing->dest_override),
                    'routeOnly' => $inbound->sniffing->route_only,
                    'metadataOnly' => $inbound->sniffing->metadata_only,
                ];
            }

            if ($inbound->policy_level_id) {
                $config['streamSettings']['sockopt']['mark'] = 0; // Default
            }

            // Protocol
            if ($inbound->vless) {
                $config['protocol'] = 'vless';
                $config['settings'] = [
                    'clients' => $inbound->vless->clients->map(fn($c) => [
                        'id' => $c->client->uuid,
                        'email' => $c->client->email,
                        'flow' => $c->flow,
                    ]),
                    'decryption' => $inbound->vless->decryption,
                    'fallbacks' => $inbound->fallbacks->map(fn($f) => [
                        'path' => $f->path,
                        'alpn' => $f->alpn,
                        'dest' => $f->dest_type === 'port' ? (int)$f->dest_value : $f->dest_value,
                        'xver' => $f->xver,
                    ]),
                ];
            }

            // Transport
            $streamSettings = [];
            if ($inbound->xhttp) {
                $streamSettings['network'] = 'xhttp';
                $streamSettings['xhttpSettings'] = [
                    'path' => $inbound->xhttp->path,
                    'mode' => $inbound->xhttp->mode,
                    'extra' => [
                        'xPaddingBytes' => $inbound->xhttp->padding_range,
                        'xPaddingObfsMode' => $inbound->xhttp->obfuscation_enabled,
                    ],
                ];
            } elseif ($inbound->grpc) {
                $streamSettings['network'] = 'grpc';
                $streamSettings['grpcSettings'] = [
                    'serviceName' => $inbound->grpc->service_name,
                    'multiMode' => $inbound->grpc->multi_mode,
                ];
            }

            // Security
            if ($inbound->tls) {
                $streamSettings['security'] = 'tls';
                $streamSettings['tlsSettings'] = [
                    'serverName' => $inbound->tls->server_name,
                    'alpn' => explode(',', $inbound->tls->alpn),
                    'allowInsecure' => $inbound->tls->allow_insecure,
                ];
            } elseif ($inbound->reality) {
                $streamSettings['security'] = 'reality';
                $streamSettings['realitySettings'] = [
                    'dest' => $inbound->reality->dest,
                    'serverNames' => explode(',', $inbound->reality->server_names),
                    'privateKey' => $inbound->reality->private_key,
                    'shortIds' => explode(',', $inbound->reality->short_ids),
                ];
            }

            if (!empty($streamSettings)) {
                $config['streamSettings'] = $streamSettings;
            }

            $inbounds[] = $config;
        }

        return $inbounds;
    }

    protected function renderOutbounds(Node $node): array
    {
        $outbounds = [];

        foreach ($node->outbounds as $outbound) {
            $config = [
                'tag' => $outbound->tag,
            ];

            if ($outbound->send_through) {
                $config['sendThrough'] = $outbound->send_through;
            }

            // Protocol
            if ($outbound->vless) {
                $config['protocol'] = 'vless';
                // Simple outbound syntax (no vnext for bridge)
                $config['settings'] = [
                    'vnext' => $outbound->vless->handler_type === 'bridge' ? null : [
                        // Traditional vnext would go here
                    ]
                ];
                
                // If bridge/reverse logic is needed
                // This is a simplified version, real IDN uses specific logic
            } else {
                // Default to freedom for direct
                $config['protocol'] = 'freedom';
                $config['settings'] = (object)[];
            }

            $outbounds[] = $config;
        }

        // Add a default freedom outbound if none exists
        if (empty($outbounds)) {
            $outbounds[] = [
                'protocol' => 'freedom',
                'tag' => 'direct',
                'settings' => (object)[],
            ];
        }

        return $outbounds;
    }

    protected function renderRouting(Node $node): array
    {
        $rules = [];
        foreach ($node->routingRules->sortBy('priority') as $rule) {
            $rules[] = [
                'type' => $rule->type,
                'inboundTag' => $rule->inbound_tags ? explode(',', $rule->inbound_tags) : null,
                'outboundTag' => $rule->outbound_tag,
                'domainStrategy' => $rule->domain_strategy,
            ];
        }

        $balancers = [];
        foreach ($node->balancers as $balancer) {
            $balancers[] = [
                'tag' => $balancer->tag,
                'selector' => explode(',', $balancer->selector),
                'strategy' => ['type' => $balancer->strategy],
            ];
        }

        return [
            'domainStrategy' => 'AsIs',
            'rules' => $rules,
            'balancers' => $balancers,
        ];
    }
}
