<?php

namespace App\Utils;

use Xray\App\Proxyman\InboundHandlerConfig;
use Xray\App\Proxyman\ReceiverConfig;
use Xray\Common\Net\IPOrDomain;
use Xray\Common\Net\PortRange;
use Xray\Common\Serial\TypedMessage;
use Google\Protobuf\Internal\Message;
use Exception;

class XrayProtobufHydrator
{
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
        $receiver->setPortRange($portRange);

        $listen = new IPOrDomain();
        $listen->setAddress($config['listen'] ?? '0.0.0.0');
        $receiver->setListen($listen);

        // Stream Settings (TLS, Transport)
        if (isset($config['streamSettings'])) {
            $streamConfig = new \Xray\Transport\Internet\StreamConfig();
            $streamConfig->mergeFromJsonString(json_encode($config['streamSettings']));
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
            $proxyMessage->mergeFromJsonString(json_encode($config['settings']));
        }

        $inbound->setProxySettings(self::wrapTypedMessage($proxyMessage, "xray.proxy.{$protocol}.ServerConfig"));

        return $inbound;
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
