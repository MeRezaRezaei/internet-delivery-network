<?php

namespace App\Utils;

use Xray\Core\InboundHandlerConfig;
use Xray\App\Proxyman\ReceiverConfig;
use Xray\Common\Net\IPOrDomain;
use Xray\Common\Net\PortRange;
use Xray\Common\Serial\TypedMessage;
use Google\Protobuf\Internal\Message;
use Exception;

class XrayProtobufHydrator
{
    /**
     * Convert a configuration array into a fully hydrated OutboundHandlerConfig.
     */
    public static function hydrateOutbound(array $config): \Xray\Core\OutboundHandlerConfig
    {
        $outbound = new \Xray\Core\OutboundHandlerConfig();
        $outbound->setTag($config['tag'] ?? throw new Exception("Outbound 'tag' is mandatory."));

        $sender = new \Xray\App\Proxyman\SenderConfig();
        $outbound->setSenderSettings(new \Xray\Common\Serial\TypedMessage([
            'type' => 'type.googleapis.com/xray.app.proxyman.SenderConfig',
            'value' => $sender->serializeToString(),
        ]));

        return $outbound;
    }

    /**
     * Convert a configuration array into a fully hydrated RoutingRule.
     */
    public static function hydrateRoutingRule(array $config): \Xray\App\Router\RoutingRule
    {
        $rule = new \Xray\App\Router\RoutingRule();
        if (isset($config['inbound_tag'])) {
            $rule->setInboundTag([$config['inbound_tag']]);
        }
        if (isset($config['outbound_tag'])) {
            $rule->setTargetTag($config['outbound_tag']);
        }
        return $rule;
    }

    /**
     * Convert a configuration array into a fully hydrated InboundHandlerConfig.
     * 
     * @param array $config Must contain 'tag', 'port', 'protocol', and optionally 'settings', 'streamSettings', 'sniffing'.
     */
    public static function hydrateInbound(array $config): InboundHandlerConfig
    {
        $inbound = new InboundHandlerConfig();
        $inbound->setTag($config['tag'] ?? throw new Exception("Inbound 'tag' is mandatory."));
        
        // --- 0. Filesystem Integrity Check ---
        self::verifyFilesystemPaths($config);

        // --- 1. Receiver Config (Network Level: Listen, Port) ---
        $receiver = new ReceiverConfig();
        
        $port = (int) ($config['port'] ?? 1080);
        $portRange = new PortRange();
        $portRange->setFrom($port);
        $portRange->setTo($port);

        $portList = new \Xray\Common\Net\PortList();
        $portList->setRange([$portRange]);
        $receiver->setPortList($portList);

        // --- Priority: Use Node Tailscale IP from Database if listen is not explicitly set ---
        $listenStr = $config['listen'] ?? '0.0.0.0';
        
        // Glue: If it's a generic listen, check if we have a Tailscale IP for this node
        if ($listenStr === '0.0.0.0' && isset($config['node_id'])) {
            $node = \App\Models\Node::find($config['node_id']);
            if ($node && $node->ip) {
                $listenStr = $node->ip;
            }
        }

        $listen = new IPOrDomain();
        if (filter_var($listenStr, FILTER_VALIDATE_IP)) {
            $listen->setIp(inet_pton($listenStr));
        } else {
            $listen->setDomain($listenStr);
        }
        $receiver->setListen($listen);

        // Stream Settings (TLS, Transport)
        if (isset($config['streamSettings'])) {
            $streamConfig = new \Xray\Transport\Internet\StreamConfig();
            
            if (isset($config['streamSettings']['network'])) {
                $network = $config['streamSettings']['network'];
                // Handle alias for httpupgrade
                if ($network === 'xhttp') {
                    $network = 'httpupgrade';
                }
                
                $streamConfig->setProtocolName($network);
                
                $transportConfig = new \Xray\Transport\Internet\TransportConfig();
                $transportConfig->setProtocolName($network);
                
                $settingsMessage = null;
                $settingsType = '';
                
                if ($network === 'httpupgrade') {
                    $settingsMessage = new \Xray\Transport\Internet\Httpupgrade\Config();
                    if (isset($config['streamSettings']['httpupgradeSettings'])) {
                        $settingsMessage->mergeFromJsonString(json_encode($config['streamSettings']['httpupgradeSettings']));
                    }
                    $settingsType = 'type.googleapis.com/xray.transport.internet.httpupgrade.Config';
                } elseif ($network === 'splithttp') {
                    $settingsMessage = new \Xray\Transport\Internet\Splithttp\Config();
                    if (isset($config['streamSettings']['splithttpSettings'])) {
                        $settingsMessage->mergeFromJsonString(json_encode($config['streamSettings']['splithttpSettings']));
                    }
                    $settingsType = 'type.googleapis.com/xray.transport.internet.splithttp.Config';
                } elseif ($network === 'grpc') {
                    $settingsMessage = new \Xray\Transport\Internet\Grpc\Encoding\Config();
                    if (isset($config['streamSettings']['grpcSettings'])) {
                        $settingsMessage->mergeFromJsonString(json_encode($config['streamSettings']['grpcSettings']));
                    }
                    $settingsType = 'type.googleapis.com/xray.transport.internet.grpc.encoding.Config';
                }
                
                if ($settingsMessage) {
                    $transportConfig->setSettings(self::wrapTypedMessage($settingsMessage, $settingsType));
                    $streamConfig->setTransportSettings([$transportConfig]);
                }
            }
            if (isset($config['streamSettings']['security'])) {
                $streamConfig->setSecurityType($config['streamSettings']['security']);
            }
            
            // Note: full TLS/Reality settings hydration requires wrapping in TypedMessage.
            // This is a simplified version for the prototype.
            
            $receiver->setStreamSettings($streamConfig);
        }

        // Sniffing
        if (isset($config['sniffing'])) {
            $sniffingConfig = new \Xray\App\Proxyman\SniffingConfig();
            $sniffingConfig->mergeFromJsonString(json_encode($config['sniffing']));
            $receiver->setSniffingSettings($sniffingConfig);
        }

        $inbound->setReceiverSettings(self::wrapTypedMessage($receiver, 'xray.app.proxyman.ReceiverConfig'));

        // Sniffing settings in Xray v26+ are often inside the receiver or a separate block.
        // For robustness, we check both.
        if (isset($config['sniffing'])) {
             $inbound->setSniffingSettings(self::wrapTypedMessage($sniffingConfig, 'xray.app.proxyman.SniffingConfig'));
        }

        // --- 2. Proxy Settings (Protocol Level: SOCKS, VLESS, etc.) ---
        $protocol = $config['protocol'] ?? 'socks';
        $proxyMessage = self::getProxyMessageForProtocol($protocol);
        
        if (isset($config['settings'])) {
            $settings = $config['settings'];
            
            // Generic handling for protocols that use 'users' or 'user' with protocol-specific accounts
            if (isset($settings['users']) || isset($settings['clients']) || isset($settings['user'])) {
                $clients = $settings['users'] ?? $settings['clients'] ?? $settings['user'] ?? [];
                
                // If it was 'user' (singular), wrap it in an array if it isn't one
                if (isset($settings['user']) && !is_array(reset($clients))) {
                    $clients = [$clients];
                }

                $users = [];
                foreach ($clients as $userData) {
                    $user = new \Xray\Common\Protocol\User();
                    $user->setEmail($userData['email'] ?? '');
                    $user->setLevel($userData['level'] ?? 0);
                    
                    $account = self::getAccountMessageForProtocol($protocol, $userData);
                    $user->setAccount(self::wrapTypedMessage($account, "xray.proxy.{$protocol}.Account"));
                    $users[] = $user;
                }

                // Call the correct setter (singular for VMess, plural for others)
                if (method_exists($proxyMessage, 'setUsers')) {
                    $proxyMessage->setUsers($users);
                } elseif (method_exists($proxyMessage, 'setUser')) {
                    $proxyMessage->setUser($users);
                }
                
                // Merge remaining settings
                unset($settings['users'], $settings['clients'], $settings['user']);
                if (!empty($settings)) {
                    $proxyMessage->mergeFromJsonString(json_encode($settings));
                }
            } else {
                $proxyMessage->mergeFromJsonString(json_encode($settings));
            }
        }

        $inbound->setProxySettings(self::wrapTypedMessage($proxyMessage, "xray.proxy.{$protocol}.ServerConfig"));

        return $inbound;
    }

    protected static function getAccountMessageForProtocol(string $protocol, array $data): Message
    {
        return match ($protocol) {
            'vless' => new \Xray\Proxy\Vless\Account([
                'id' => $data['id'] ?? '',
                'flow' => $data['flow'] ?? '',
                'encryption' => $data['encryption'] ?? '',
            ]),
            'vmess' => new \Xray\Proxy\Vmess\Account([
                'id' => $data['id'] ?? '',
                'security_settings' => isset($data['security']) ? 
                    new \Xray\Common\Protocol\SecurityConfig(['type' => match($data['security']) {
                        'aes-128-gcm' => \Xray\Common\Protocol\SecurityType::AES128_GCM,
                        'chacha20-poly1305' => \Xray\Common\Protocol\SecurityType::CHACHA20_POLY1305,
                        'auto' => \Xray\Common\Protocol\SecurityType::AUTO,
                        default => \Xray\Common\Protocol\SecurityType::NONE
                    }]) : null
            ]),
            default => throw new Exception("Account hydration for [{$protocol}] not implemented."),
        };
    }

    /**
     * Recursively find and verify any file paths in the config (e.g. certificates).
     */
    protected static function verifyFilesystemPaths(array $config): void
    {
        array_walk_recursive($config, function ($value, $key) {
            // Check for common certificate/key keys
            if (in_array($key, ['certificateFile', 'keyFile', 'path'])) {
                if (!is_string($value)) return;
                
                // Absolute paths or paths starting with /etc/ (common in Xray)
                if (str_starts_with($value, '/')) {
                    if (!file_exists($value)) {
                        throw new Exception("Config Error: File not found at [{$value}]. Ensure the path is correct on the node's filesystem.");
                    }
                }
            }
        });
    }

    protected static function getProxyMessageForProtocol(string $protocol): Message
    {
        return match ($protocol) {
            'socks' => new \Xray\Proxy\Socks\ServerConfig(),
            'vless' => new \Xray\Proxy\Vless\Inbound\Config(),
            'vmess' => new \Xray\Proxy\Vmess\Inbound\Config(),
            'shadowsocks' => new \Xray\Proxy\Shadowsocks\ServerConfig(),
            'trojan' => new \Xray\Proxy\Trojan\ServerConfig(),
            'dokodemo-door' => new \Xray\Proxy\Dokodemo\Config(),
            default => throw new Exception("Protocol [{$protocol}] is not yet supported in Control Plane hydration."),
        };
    }

    public static function wrapTypedMessage(Message $message, string $type): TypedMessage
    {
        $typed = new TypedMessage();
        $typed->setType($type);
        $typed->setValue($message->serializeToString());
        return $typed;
    }
}
