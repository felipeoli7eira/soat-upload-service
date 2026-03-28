<?php

namespace App\Infrastructure\Queue;

use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use RuntimeException;
use Throwable;

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

        $this->connect();
    }

    public function connect(): void
    {
        $this->connection = new AMQPStreamConnection(
            env("RABBITMQ_HOST"),
            env("RABBITMQ_PORT"),
            env("RABBITMQ_USER"),
            env("RABBITMQ_PASSWORD"),
            env("RABBITMQ_VHOST"),
            true
        );
    }

    // public function __destruct()
    // {
    //     logger()->info("RabbitMQ Message Broker stopped.", []);

    //     if ($this->connection) {
    //         $this->connection->close();
    //     }
    // }

    public function createChannel()
    {
        if (! $this->connection || ! $this->connection?->isConnected()) {
            $this->reconnect();
        }

        return $this->connection->channel();
    }

    private function reconnect(): void
    {
        $attempts = 0;

        while ($attempts < 5) {
            try {
                sleep(2 ** $attempts);
                $this->connect();
                return;
            } catch (Throwable $e) {
                $attempts++;
                logger()->warning("RabbitMQ reconnect attempt {$attempts}", ['err' => $e->getMessage()]);
            }
        }

        throw new RuntimeException("RabbitMQ: falhou ao reconectar após 5 tentativas");
    }

    public function setup(): void
    {
        $channel = $this->createChannel();

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
    }

    public function publishUpload(array $messagePayload): bool
    {
        if (sizeof($messagePayload) === 0) return false;

        $channel = $this->createChannel();

        $channel->basic_publish(
            msg: new AMQPMessage(json_encode($messagePayload), [
                'content_type'  => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, // ! IMPORTANTE: persiste no disco
            ]),

            exchange: "default",

            routing_key: "diagram_queue_routing_key", // o routing_key deve ser o nome da fila para onde a mensagem vai diretamente.

            mandatory: false, // Se true, exige que a mensagem seja roteada para pelo menos uma fila. Caso contrário, o RabbitMQ retorna a mensagem ao publicador via basic.return
            // false (padrão): se a mensagem não puder ser roteada, ela é descartada silenciosamente.
            // true: se nenhuma fila receber a mensagem, ela é devolvida ao publicador, que deve tratar o retorno (ex: log, re-publicação).
            // Útil para garantir que a mensagem não seja perdida por erro de roteamento.

            immediate: false, // exige que a mensagem seja entregue imediatamente a um consumidor. Se não houver consumidor pronto, a mensagem é retornada.
            // Este parâmetro é obsoleto no RabbitMQ (removido na versão 3.0+). Definir como true geralmente resulta em erro ou é ignorado.
            // Manter como false.

            ticket: null // Usado em versões antigas do RabbitMQ com ACL (Access Control Lists). Representa um ticket de autenticação.
            // Em versões recentes do RabbitMQ e da biblioteca, esse parâmetro não é mais utilizado. Deixar como null.
        );

        if ($channel) {
            $channel->close();
        }

        return true;
    }
}
