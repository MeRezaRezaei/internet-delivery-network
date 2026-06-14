<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Marzban\User;
use App\Models\Marzban\Proxy;

// 1. First, require Google Protobuf and real Xray Protobuf generated files if they exist.
// This registers the real classes in memory BEFORE any stubs or autoloaders trigger class_exists checks.
$googleProtobufFile = __DIR__ . '/../vendor/google/protobuf/src/Google/Protobuf/RepeatedField.php';
if (file_exists($googleProtobufFile)) {
    require_once $googleProtobufFile;
}

$xrayProtobufPath = __DIR__ . '/../app/Protobuf/';
$xrayProtobufFiles = [
    'Xray/Core/InboundHandlerConfig.php',
    'Xray/App/Proxyman/Command/HandlerServiceClient.php',
    'Xray/App/Proxyman/Command/RemoveInboundRequest.php',
    'Xray/App/Proxyman/Command/AddInboundRequest.php',
    'Xray/App/Proxyman/Command/ListInboundsRequest.php',
    'Xray/App/Stats/Command/StatsServiceClient.php',
    'Xray/App/Stats/Command/QueryStatsRequest.php',
    'Xray/App/Stats/Command/SysStatsRequest.php',
];

foreach ($xrayProtobufFiles as $file) {
    $path = $xrayProtobufPath . $file;
    if (file_exists($path)) {
        require_once $path;
    }
}

// 2. Define global gRPC constants.
if (!defined('Grpc\OP_SEND_INITIAL_METADATA')) {
    define('Grpc\OP_SEND_INITIAL_METADATA', 0);
    define('Grpc\OP_SEND_MESSAGE', 1);
    define('Grpc\OP_SEND_CLOSE_FROM_CLIENT', 2);
    define('Grpc\OP_SEND_STATUS_FROM_SERVER', 3);
    define('Grpc\OP_RECV_INITIAL_METADATA', 4);
    define('Grpc\OP_RECV_MESSAGE', 5);
    define('Grpc\OP_RECV_STATUS_ON_CLIENT', 6);
    define('Grpc\OP_RECV_CLOSE_ON_SERVER', 7);
}

// 3. Dynamically define gRPC stubs if the grpc extension is missing.
if (!class_exists('Grpc\ChannelCredentials', true)) {
    eval('
        namespace Grpc {
            class ChannelCredentials {
                public static function createInsecure() { return new self(); }
                public static function setDefaultRootsPem($pem) {}
            }
        }
    ');
}

if (!class_exists('Grpc\Channel', true)) {
    eval('
        namespace Grpc {
            class Channel {
                public function __construct($hostname, $options) {}
                public function getTarget() { return ""; }
                public function getConnectivityState() { return 0; }
                public function watchConnectivityState($last_state, $deadline) { return true; }
                public function close() {}
            }
        }
    ');
}

if (!class_exists('Grpc\Timeval', true)) {
    eval('
        namespace Grpc {
            class Timeval {
                public static function now() { return new self(); }
                public static function infFuture() { return new self(); }
                public function add($timeval) { return $this; }
            }
        }
    ');
}

if (!class_exists('Grpc\Call', true)) {
    eval('
        namespace Grpc {
            class Call {
                public static $lastTags = [];
                protected $channel;
                protected $method;
                protected $options;

                public function __construct($channel, $method, $options) {
                    $this->channel = $channel;
                    $this->method = $method;
                    $this->options = $options;
                }

                public function startBatch(array $batch) {
                    $event = new \stdClass();
                    $event->metadata = [];
                    $event->status = new \stdClass();
                    $event->status->code = 0;
                    $event->status->details = "OK";
                    $event->status->metadata = [];
                    
                    if (isset($batch[1])) { // OP_SEND_MESSAGE
                        $msgBytes = $batch[1]["message"] ?? "";
                        if (preg_match(\'/([a-zA-Z0-9_-]{8,})/\', $msgBytes, $matches)) {
                            self::$lastTags[] = $matches[1];
                        }
                    }
                    
                    $isListInbounds = str_contains($this->method, "ListInbounds");
                    $isRemoveInbound = str_contains($this->method, "RemoveInbound");
                    $isSysStats = str_contains($this->method, "SysStats");
                    $isQueryStats = str_contains($this->method, "QueryStats");
                    
                    if ($isRemoveInbound) {
                        $tag = "";
                        if (isset($batch[1])) {
                            $msgBytes = $batch[1]["message"] ?? "";
                            if (preg_match(\'/([a-zA-Z0-9_-]{8,})/\', $msgBytes, $matches)) {
                                $tag = $matches[1];
                            }
                        }
                        if (!in_array($tag, self::$lastTags)) {
                            $event->status->code = 5;
                            $event->status->details = "Inbound handler not found";
                        } else {
                            self::$lastTags = array_values(array_diff(self::$lastTags, [$tag]));
                        }
                    }
                    
                    if ($isListInbounds && class_exists("Xray\App\Proxyman\Command\ListInboundsResponse")) {
                        $response = new \Xray\App\Proxyman\Command\ListInboundsResponse();
                        $inbounds = [];
                        foreach (self::$lastTags as $tag) {
                            $inbound = new \Xray\App\Proxyman\InboundHandlerConfig();
                            $inbound->setTag($tag);
                            $inbounds[] = $inbound;
                        }
                        $inboundDefault = new \Xray\App\Proxyman\InboundHandlerConfig();
                        $inboundDefault->setTag("Advanced Host");
                        $inbounds[] = $inboundDefault;
                        
                        $response->setInbounds($inbounds);
                        $event->message = $response->serializeToString();
                    } elseif ($isSysStats && class_exists("Xray\App\Stats\Command\SysStatsResponse")) {
                        $response = new \Xray\App\Stats\Command\SysStatsResponse();
                        $response->setUptime(100);
                        $response->setNumGoroutine(5);
                        $response->setAlloc(1000);
                        $response->setTotalAlloc(2000);
                        $response->setSys(3000);
                        $response->setMallocs(400);
                        $response->setFrees(300);
                        $response->setLiveObjects(100);
                        $response->setNumGC(1);
                        $response->setPauseTotalNs(10);
                        $event->message = $response->serializeToString();
                    } elseif ($isQueryStats && class_exists("Xray\App\Stats\Command\QueryStatsResponse")) {
                        $response = new \Xray\App\Stats\Command\QueryStatsResponse();
                        $response->setStat([]);
                        $event->message = $response->serializeToString();
                    } else {
                        $event->message = "";
                    }
                    
                    return $event;
                }
            }
        }
    ');
}

// 4. Stub Redis class if extension is not installed.
if (!class_exists('Redis', true)) {
    eval('
        class Redis {
            protected static $keys = [];
            protected static $hashes = [];

            public function connect($host, $port = 6379, $timeout = 0.0, $reserved = null, $retry_interval = 0, $read_timeout = 0.0) { return true; }
            public function select($db) { return true; }
            public function get($key) { return self::$keys[$key] ?? null; }
            public function set($key, $value, $timeout = null) { self::$keys[$key] = (string)$value; return true; }
            public function del($key) { unset(self::$keys[$key]); return 1; }
            public function ping() { return "+PONG"; }
            public function hset($key, $member, $value = null) { self::$hashes[$key][$member] = (string)$value; return 1; }
            public function hget($key, $member) { return self::$hashes[$key][$member] ?? null; }
            public function hgetall($key) { return self::$hashes[$key] ?? []; }
            public function rawCommand($command, ...$arguments) { return "12345-1"; }
            public function flushdb() { self::$keys = []; self::$hashes = []; return true; }
            public function expire($key, $ttl) { return true; }
            public function executeRaw($args = []) { return "12345-1"; }
            public function keys($pattern) {
                $allKeys = array_unique(array_merge(array_keys(self::$keys), array_keys(self::$hashes)));
                return array_values(array_filter($allKeys, function($key) use ($pattern) {
                    return fnmatch($pattern, $key);
                }));
            }
        }
    ');
}

// 5. Stub RepeatedField if class is missing.
if (!class_exists('Google\Protobuf\Internal\RepeatedField', true)) {
    eval('
        namespace Google\Protobuf\Internal {
            class RepeatedField {}
        }
    ');
}

abstract class TestCase extends BaseTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        // Force test database configuration to guarantee total isolation
        config(['database.connections.mysql.driver' => 'mysql']);
        config(['database.connections.mysql.host' => '127.0.0.1']);
        config(['database.connections.mysql.port' => '3307']);
        config(['database.connections.mysql.database' => 'idn_db']);
        config(['database.connections.mysql.username' => 'idn_user']);
        config(['database.connections.mysql.password' => 'idn_password']);

        config(['database.connections.marzban.driver' => 'mysql']);
        config(['database.connections.marzban.host' => '127.0.0.1']);
        config(['database.connections.marzban.port' => '3307']);
        config(['database.connections.marzban.database' => 'idn_db']);
        config(['database.connections.marzban.username' => 'idn_user']);
        config(['database.connections.marzban.password' => 'idn_password']);

        parent::setUp();

        $this->seedTestDatabase();
    }

    protected function seedTestDatabase(): void
    {
        if (\Schema::hasTable('users')) {
            $user = new User();
            $user->username = 'testuser';
            $user->status = 'active';
            $user->save();

            $proxy = new Proxy();
            $proxy->user_id = $user->id;
            $proxy->type = 'VLESS';
            $proxy->settings = [
                'id' => '07620f06-ab92-4752-9110-1a573605839b',
                'flow' => '',
            ];
            $proxy->save();
        }
    }
}
