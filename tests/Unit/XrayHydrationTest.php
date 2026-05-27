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
                'clients' => [
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
                            'certificateFile' => '/etc/xray/cert.pem',
                            'keyFile' => '/etc/xray/key.pem'
                        ]
                    ]
                ]
            ]
        ];

        // Create dummy files to pass the filesystem check
        if (!file_exists('/etc/xray')) {
            mkdir('/etc/xray', 0777, true);
        }
        touch('/etc/xray/cert.pem');
        touch('/etc/xray/key.pem');

        $inbound = XrayProtobufHydrator::hydrateInbound($config);

        $this->assertEquals('vless-inbound', $inbound->getTag());
        
        // Clean up
        unlink('/etc/xray/cert.pem');
        unlink('/etc/xray/key.pem');
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
