<?php

namespace App\Infrastructure\Queue;

use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class RabbitMQ
{
    public readonly AMQPStreamConnection $connection;

    public function __construct()
    {
        logger()->info("RabbitMQ Message Broker started.", []);

        if (in_array(null, [
            env("RABBITMQ_HOST"),
            env("RABBITMQ_PORT"),
            env("RABBITMQ_USER"),
            env("RABBITMQ_PASSWORD"),
            env("RABBITMQ_VHOST"),
        ])) {
            throw new Exception("Message Broker nao configurado corretamente.", 1);
        }

        $this->connection = new AMQPStreamConnection(
            env("RABBITMQ_HOST"),
            env("RABBITMQ_PORT"),
            env("RABBITMQ_USER"),
            env("RABBITMQ_PASSWORD"),
            env("RABBITMQ_VHOST"),
            true
        );
    }

    public function __destruct()
    {
        logger()->info("RabbitMQ Message Broker stopped.", []);

        if ($this->connection) {
            $this->connection->close();
        }
    }

    public function setup(): void
    {
        $channel = $this->connection->channel();

        $channel->exchange_declare("default", "direct", false, true, false);

        $channel->queue_declare(
            "diagrams",
            false,
            true,
            false,
            false,
        );

        $channel->queue_bind(
            "diagrams",
            "default",
            "diagram_queue_routing_key",
        );

        if ($channel) {
            $channel->close();
        }

        if ($this->connection) {
            $this->connection->close();
        }
    }

    public function publishUpload(array $messagePayload): bool
    {
        if (sizeof($messagePayload) === 0) return false;

        $channel = $this->connection->channel();

        if ($channel) {
            $channel->close();
        }

        if ($this->connection) {
            $this->connection->close();
        }

        return true;
    }
}
