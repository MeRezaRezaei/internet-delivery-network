<?php

namespace Tests\Unit;

use App\Utils\XrayProtobufHydrator;
use Tests\TestCase;

class XrayHydrationTest extends TestCase
{
    /**
     * Test hydration of a complex VLESS + XTLS config.
     */
    public function test_vless_xtls_hydration()
    {
        $config = [
            'tag' => 'vless-inbound',
            'port' => 443,
            'protocol' => 'vless',
            'settings' => [
                'users' => [
                    ['id' => '121c2557-410a-48f3-8b77-8d0702d76378', 'flow' => 'xtls-rprx-vision']
                ],
                'decryption' => 'none'
            ],
            'streamSettings' => [
                'network' => 'tcp',
                'security' => 'tls',
                'tlsSettings' => [
                    'certificates' => [
                        [
                            'certificateFile' => '/tmp/xray/cert.pem',
                            'keyFile' => '/tmp/xray/key.pem'
                        ]
                    ]
                ]
            ]
        ];

        // Create dummy files to pass the filesystem check
        if (!file_exists('/tmp/xray')) {
            mkdir('/tmp/xray', 0777, true);
        }
        touch('/tmp/xray/cert.pem');
        touch('/tmp/xray/key.pem');

        $inbound = XrayProtobufHydrator::hydrateInbound($config);

        $this->assertEquals('vless-inbound', $inbound->getTag());
        
        // Clean up
        unlink('/tmp/xray/cert.pem');
        unlink('/tmp/xray/key.pem');
        rmdir('/tmp/xray');
    }

    /**
     * Test VMess hydration with 'user' (singular) field and security settings.
     */
    public function test_vmess_hydration()
    {
        $config = [
            'tag' => 'vmess-inbound',
            'port' => 444,
            'protocol' => 'vmess',
            'settings' => [
                'user' => [
                    'id' => '121c2557-410a-48f3-8b77-8d0702d76378',
                    'security' => 'aes-128-gcm'
                ]
            ]
        ];

        $inbound = XrayProtobufHydrator::hydrateInbound($config);
        
        $this->assertEquals('vmess-inbound', $inbound->getTag());
        $this->assertNotNull($inbound->getProxySettings());
    }

    /**
     * Test xhttp (httpupgrade) and splithttp transport hydration.
     */
    public function test_transport_hydration()
    {
        $config = [
            'tag' => 'xhttp-inbound',
            'port' => 443,
            'protocol' => 'vless',
            'settings' => [
                'users' => [['id' => 'b831381d-6324-4d53-ad4f-8cda48b30811', 'encryption' => 'none']]
            ],
            'streamSettings' => [
                'network' => 'xhttp',
                'httpupgradeSettings' => [
                    'host' => 'example.com',
                    'path' => '/secret'
                ]
            ]
        ];

        $inbound = \App\Utils\XrayProtobufHydrator::hydrateInbound($config);
        $this->assertEquals('xhttp-inbound', $inbound->getTag());
        
        $receiver = new \Xray\App\Proxyman\ReceiverConfig();
        $receiver->mergeFromString($inbound->getReceiverSettings()->getValue());
        
        $streamSettings = $receiver->getStreamSettings();
        $this->assertEquals('httpupgrade', $streamSettings->getProtocolName());
        
        $transportSettings = $streamSettings->getTransportSettings();
        $this->assertCount(1, $transportSettings);
        $this->assertEquals('httpupgrade', $transportSettings[0]->getProtocolName());
        $this->assertEquals('type.googleapis.com/xray.transport.internet.httpupgrade.Config', $transportSettings[0]->getSettings()->getType());

        // Test SplitHTTP
        $config['streamSettings']['network'] = 'splithttp';
        $config['streamSettings']['splithttpSettings'] = [
            'host' => 'example.com',
            'path' => '/split'
        ];
        unset($config['streamSettings']['httpupgradeSettings']);

        $inboundSplit = \App\Utils\XrayProtobufHydrator::hydrateInbound($config);
        $receiverSplit = new \Xray\App\Proxyman\ReceiverConfig();
        $receiverSplit->mergeFromString($inboundSplit->getReceiverSettings()->getValue());
        
        $streamSettingsSplit = $receiverSplit->getStreamSettings();
        $this->assertEquals('splithttp', $streamSettingsSplit->getProtocolName());
    }

    /**
     * Test that invalid paths throw exceptions.
     */
    public function test_invalid_path_throws_exception()
    {
        $config = [
            'tag' => 'bad-path-inbound',
            'port' => 8080,
            'protocol' => 'socks',
            'streamSettings' => [
                'tlsSettings' => [
                    'certificates' => [['certificateFile' => '/non/existent/path.pem']]
                ]
            ]
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File not found');

        XrayProtobufHydrator::hydrateInbound($config);
    }
}
