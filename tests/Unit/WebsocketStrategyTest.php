<?php

namespace Tests\Unit;

use App\Support\Broadcasting\WebsocketStrategy;
use Illuminate\Config\Repository;
use Tests\TestCase;

class WebsocketStrategyTest extends TestCase
{
    public function test_it_builds_current_pusher_compatible_self_hosted_strategy_from_config(): void
    {
        $config = new Repository([
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher' => [
                'driver' => 'pusher',
                'key' => 'test-key',
                'secret' => 'test-secret',
                'app_id' => 'test-app',
                'options' => [
                    'cluster' => 'ap2',
                    'encrypted' => true,
                    'host' => '127.0.0.1',
                    'port' => 6001,
                    'scheme' => 'https',
                ],
            ],
            'websockets' => [
                'path' => 'laravel-websockets',
                'apps' => [
                    [
                        'enable_statistics' => true,
                    ],
                ],
            ],
        ]);

        $strategy = WebsocketStrategy::fromConfig($config);

        $this->assertSame('pusher', $strategy->driver());
        $this->assertTrue($strategy->isPusherCompatible());
        $this->assertTrue($strategy->usesSelfHostedServer());
        $this->assertSame('laravel-websockets', $strategy->dashboardPath());
        $this->assertTrue($strategy->statisticsEnabled());
        $this->assertSame([
            'broadcaster' => 'pusher',
            'key' => 'test-key',
            'cluster' => 'ap2',
            'host' => '127.0.0.1',
            'port' => 6001,
            'scheme' => 'https',
            'encrypted' => true,
        ], $strategy->clientConfig());
    }

    public function test_it_reports_disabled_strategy_when_broadcasting_is_null(): void
    {
        $config = new Repository([
            'broadcasting.default' => 'null',
            'broadcasting.connections.null' => [
                'driver' => 'null',
            ],
            'websockets' => [],
        ]);

        $strategy = WebsocketStrategy::fromConfig($config);

        $this->assertSame('null', $strategy->driver());
        $this->assertTrue($strategy->isDisabled());
        $this->assertFalse($strategy->isPusherCompatible());
        $this->assertFalse($strategy->usesSelfHostedServer());
        $this->assertNull($strategy->dashboardPath());
        $this->assertFalse($strategy->statisticsEnabled());
        $this->assertSame([
            'broadcaster' => 'null',
        ], $strategy->clientConfig());
    }

    public function test_it_distinguishes_external_pusher_strategy_from_self_hosted_server(): void
    {
        $config = new Repository([
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher' => [
                'driver' => 'pusher',
                'key' => 'test-key',
                'options' => [
                    'cluster' => 'mt1',
                    'encrypted' => true,
                    'host' => 'ws.pusherapp.com',
                    'port' => 443,
                    'scheme' => 'https',
                ],
            ],
            'websockets' => [
                'path' => 'laravel-websockets',
                'apps' => [],
            ],
        ]);

        $strategy = WebsocketStrategy::fromConfig($config);

        $this->assertTrue($strategy->isPusherCompatible());
        $this->assertFalse($strategy->usesSelfHostedServer());
    }
}
