<?php

namespace App\Utils;

use Xray\App\Proxyman\InboundHandlerConfig;
use Xray\App\Proxyman\ReceiverConfig;
use Xray\Common\Net\IPOrDomain;
use Xray\Common\Net\PortRange;
use Xray\Common\Serial\TypedMessage;
use Xray\Proxy\Socks\ServerConfig;
use Google\Protobuf\Internal\Message;
use Exception;

class XrayProtobufHydrator
{
    /**
     * Convert a simple array configuration into an InboundHandlerConfig.
     * This is a simplified prototype focused on SOCKS for demonstration.
     */
    public static function hydrateInbound(array $config): InboundHandlerConfig
    {
        $inbound = new InboundHandlerConfig();
        $inbound->setTag($config['tag'] ?? 'dynamic-inbound');
        
        // Receiver Config (Listen IP, Port)
        $receiver = new ReceiverConfig();
        
        $portRange = new PortRange();
        $portRange->setFrom($config['port'] ?? 1080);
        $portRange->setTo($config['port'] ?? 1080);
        $receiver->setPortRange($portRange);

        $listen = new IPOrDomain();
        $listen->setAddress($config['listen'] ?? '0.0.0.0');
        $receiver->setListen($listen);

        $inbound->setReceiverSettings(self::wrapTypedMessage($receiver, 'xray.app.proxyman.ReceiverConfig'));

        // Proxy Settings (e.g. SOCKS)
        $protocol = $config['protocol'] ?? 'socks';
        if ($protocol === 'socks') {
            $proxyConfig = new ServerConfig();
            $inbound->setProxySettings(self::wrapTypedMessage($proxyConfig, 'xray.proxy.socks.ServerConfig'));
        } else {
            throw new Exception("Protocol [{$protocol}] hydration not yet supported in this prototype.");
        }

        return $inbound;
    }

    /**
     * Wrap a protobuf message into a TypedMessage (Xray's 'Any' equivalent).
     */
    public static function wrapTypedMessage(Message $message, string $type): TypedMessage
    {
        $typed = new TypedMessage();
        $typed->setType($type);
        $typed->setValue($message->serializeToString());
        return $typed;
    }
}
