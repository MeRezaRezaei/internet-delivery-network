<?php

return [
    'templates' => [
        'direct_xhttp' => [
            'address' => 'srv-1.docsub.ir',
            'port' => 2096,
            'sni' => 'srv-1.docsub.ir',
            'host' => 'srv-1.docsub.ir',
            'path' => '/',
            'security' => 'tls',
            'insecure' => 1,
            'mode' => 'packet-up',
            'extra' => [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                ],
                'xPaddingBytes' => '100-500',
                'noGRPCHeader' => false,
                'scMaxEachPostBytes' => '1000000-2000000',
                'scMinPostsIntervalMs' => '50-150',
                'xmux' => [
                    'maxConcurrency' => '8-16',
                    'maxConnections' => 0,
                    'cMaxReuseTimes' => '10-20',
                    'hMaxRequestTimes' => '100-300',
                    'hMaxReusableSecs' => '120-240',
                    'hKeepAlivePeriod' => 0,
                ],
                'downloadSettings' => [
                    'address' => 'i-01.docdns.ir',
                    'port' => 2096,
                    'network' => 'xhttp',
                    'security' => 'tls',
                    'tlsSettings' => [
                        'serverName' => 'i-01.docdns.ir',
                        'certificates' => [
                            [
                                'certificate' => [
                                    "-----BEGIN CERTIFICATE-----\nMIIDLDCCAhSgAwIBAgIUS5XUtjFKq/mIF5KLpkXuxol1Rf8wDQYJKoZIhvcNAQEL\nBQAwFDESMBAGA1UEAwwJZG9jc3ViLmlyMB4XDTI2MDYwMjEwMzUxMloXDTM2MDUz\nMDEwMzUxMlowFDESMBAGA1UEAwwJZG9jc3ViLmlyMIIBIjANBgkqhkiG9w0BAQEF\nAAOCAQ8AMIIBCgKCAQEAzjbZ3WVgv4dJJ/CYR2LeSZw/ZEL9I8rLvSlRVl1Ce5GK\n05jG7tRWQGoHroP0gVn5TeDYNoV3gO3E/uUtrVxs59UJXkWpQReOb2lehluQpWWH\nZ92wMQlkfzf617C86RZBBpSILHBapYkt9SGH6AiRC0UXQFCsE+koqMGxbi/ZJuF+\nJbGCuxxh5g2Gemm8H9dnSBli4+0X5qmJEp6rAhDjiuvYNsjOUM6kqMh5O74SkyOD\nvbEhAZqPUIMCgXBdGsN2EovfY163EI/m2gnlkCauO6ilYhvtWCxDQnnB9wbpbRVi\nrvzS1mapSRS6NrXsZas2HdcOUSSegwQ1fTMEtKC0mQIDAQABo3YwdDAdBgNVHQ4E\nFgQUz187Mtkswo11eKEtl4O5cioa1ZIwHwYDVR0jBBgwFoAUz187Mtkswo11eKEt\nl4O5cioa1ZIwDwYDVR0TAQH/BAUwAwEB/zAhBgNVHREEGjAYgglkb2NkbnMuaXKC\nCyouZG9jZG5zLmlyMA0GCSqGSIb3DQEBCwUAA4IBAQA5laXwJwOkdxTh+ZNtE6f/\nE1Zfdfpdpf2A03PwfekvXslg0bsG9sLYb8jfxeepEtipXQvaOLDw1Lq78Nqk+a+F\nNIuQBjlsoPv4L6guoBlQlCK9to2DJ+paDbNUJ0AQQeNhQEQD73+MGsueGUwuD17l\nutqk1yXDe7RNMqV2ldrgTGT+FYL5ZyjpEpdcYhDx1S/ZDZRp0+PrmLz4oglybxt3\nqVVY2r5oXZ7slTGQqE6zGSEoW5U5HAkUf0rvh1p8Ys/DkQmHaMiRA4tqYIAgUYU9\nNwcl6Rkat8R+g3RtDBTdU/lnCOF3xhLQ3CT8NLgnWdvxtyQmvFCp4OpkdMvFuqNc\n-----END CERTIFICATE-----\n"
                                ]
                            ]
                        ],
                        'alpn' => ['h2', 'http/1.1'],
                        'disableSystemRoot' => true,
                    ],
                    'xhttpSettings' => [
                        'path' => '/',
                        'mode' => 'packet-up',
                        'extra' => [
                            'headers' => [
                                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                            ],
                            'xPaddingBytes' => '100-500',
                            'noGRPCHeader' => false,
                            'scMaxEachPostBytes' => '1000000-2000000',
                            'scMinPostsIntervalMs' => '50-150',
                            'xmux' => [
                                'maxConcurrency' => '8-16',
                                'maxConnections' => 0,
                                'cMaxReuseTimes' => '10-20',
                                'hMaxRequestTimes' => '100-300',
                                'hMaxReusableSecs' => '120-240',
                                'hKeepAlivePeriod' => 0,
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'reverse_xhttp' => [
            'address' => 'i-09.menudigi.ir',
            'port' => 8443,
            'sni' => 'i-09.menudigi.ir',
            'host' => 'i-09.menudigi.ir',
            'path' => '/',
            'security' => 'tls',
            'insecure' => 0,
            'mode' => 'packet-up',
            'extra' => [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                ],
                'xPaddingBytes' => '100-500',
                'noGRPCHeader' => false,
                'scMaxEachPostBytes' => '1000000-2000000',
                'scMinPostsIntervalMs' => '50-150',
                'xmux' => [
                    'maxConcurrency' => '8-16',
                    'maxConnections' => 0,
                    'cMaxReuseTimes' => '10-20',
                    'hMaxRequestTimes' => '100-300',
                    'hMaxReusableSecs' => '120-240',
                    'hKeepAlivePeriod' => 0,
                ],
                'downloadSettings' => [
                    'address' => 'i-09.myavestar.ir',
                    'port' => 8443,
                    'network' => 'xhttp',
                    'security' => 'tls',
                    'tlsSettings' => [
                        'serverName' => 'i-09.myavestar.ir',
                        'alpn' => ['h2', 'http/1.1'],
                    ],
                    'xhttpSettings' => [
                        'path' => '/',
                        'mode' => 'packet-up',
                        'extra' => [
                            'headers' => [
                                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                            ],
                            'xPaddingBytes' => '100-500',
                            'noGRPCHeader' => false,
                            'scMaxEachPostBytes' => '1000000-2000000',
                            'scMinPostsIntervalMs' => '50-150',
                            'xmux' => [
                                'maxConcurrency' => '8-16',
                                'maxConnections' => 0,
                                'cMaxReuseTimes' => '10-20',
                                'hMaxRequestTimes' => '100-300',
                                'hMaxReusableSecs' => '120-240',
                                'hKeepAlivePeriod' => 0,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
