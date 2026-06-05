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
