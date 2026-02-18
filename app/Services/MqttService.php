<?php

namespace App\Services;

use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;

class MqttService
{
    protected MqttClient $client;
    protected bool $connected = false;
    protected ConnectionSettings $connectionSettings;

    public function __construct()
    {
        $server   = config('mqtt.host', 'broker.emqx.io');
        $port     = (int) config('mqtt.port', 1883);
        $clientId = 'truelysell_' . uniqid();
        $connectionSettings = (new ConnectionSettings())
            ->setKeepAliveInterval(60)
            ->setLastWillTopic(null)
            ->setUsername(null)
            ->setPassword(null);

        $this->client = new MqttClient($server, $port, $clientId);
        $this->connectionSettings = $connectionSettings;
    }

    public function connect(): void
    {
        if (!$this->connected) {
            $this->client->connect($this->connectionSettings, true);
            $this->connected = true;
        }
    }

    public function publish(string $topic, string $message): void
    {
        $this->connect();
        $this->client->publish($topic, $message);
    }

    public function disconnect(): void
    {
        if ($this->connected) {
            $this->client->disconnect();
            $this->connected = false;
        }
    }
}
