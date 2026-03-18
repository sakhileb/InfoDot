<?php

namespace App\Support\Broadcasting;

use Illuminate\Contracts\Config\Repository;

class WebsocketStrategy
{
    private string $defaultDriver;

    private array $connection;

    private array $websockets;

    public function __construct(string $defaultDriver, array $connection = [], array $websockets = [])
    {
        $this->defaultDriver = $defaultDriver;
        $this->connection = $connection;
        $this->websockets = $websockets;
    }

    public static function fromConfig(Repository $config): self
    {
        $defaultDriver = (string) $config->get('broadcasting.default', 'null');
        $connection = (array) $config->get("broadcasting.connections.{$defaultDriver}", []);
        $websockets = (array) $config->get('websockets', []);

        return new self($defaultDriver, $connection, $websockets);
    }

    public function driver(): string
    {
        return $this->defaultDriver;
    }

    public function isDisabled(): bool
    {
        return $this->defaultDriver === 'null';
    }

    public function isPusherCompatible(): bool
    {
        return $this->defaultDriver === 'pusher';
    }

    public function usesSelfHostedServer(): bool
    {
        if (! $this->isPusherCompatible()) {
            return false;
        }

        $host = (string) ($this->connection['options']['host'] ?? '');

        return in_array($host, ['127.0.0.1', 'localhost'], true);
    }

    public function clientConfig(): array
    {
        if (! $this->isPusherCompatible()) {
            return [
                'broadcaster' => $this->defaultDriver,
            ];
        }

        $options = (array) ($this->connection['options'] ?? []);

        return [
            'broadcaster' => 'pusher',
            'key' => $this->connection['key'] ?? null,
            'cluster' => $options['cluster'] ?? null,
            'host' => $options['host'] ?? null,
            'port' => $options['port'] ?? null,
            'scheme' => $options['scheme'] ?? null,
            'encrypted' => (bool) ($options['encrypted'] ?? false),
        ];
    }

    public function dashboardPath(): ?string
    {
        return $this->websockets['path'] ?? null;
    }

    public function statisticsEnabled(): bool
    {
        return (bool) ($this->websockets['apps'][0]['enable_statistics'] ?? false);
    }
}
