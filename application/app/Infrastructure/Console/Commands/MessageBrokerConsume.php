<?php

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Queue\RabbitMQ;
use Exception;
use Illuminate\Console\Command;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class MessageBrokerConsume extends Command
{
    protected $signature = 'queue:consume';
    protected $description = 'Consumes messages from the Message Broker that the application is using (initially designed to use RabbitMQ).';

    public function __construct(private RabbitMQ $broker)
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $this->info("Consuming messages from the Message Broker...");
            $this->broker->consumeUploads(function (AMQPMessage $message, AMQPChannel $channel) {
                logger()->info("Message consumed from the uploads queue.", [
                    "message" => $message->getBody(),
                ]);

                // Here you can implement the logic to process the message, such as updating the upload status in the database, sending notifications, etc.

                try {
                    throw new Exception("Simulated error while processing the message."); // Simulando um erro para testar a DLQ
                } catch (Throwable $err) {
                    $channel->basic_nack( // NACK → falhou no processamento da mensagem
                        $message->getDeliveryTag(),
                        false, // multiple: se true, rejeita todas as mensagens até a mensagem atual (inclusive). Se false, rejeita apenas a mensagem atual.
                        false // requeue: se true, a mensagem rejeitada será reencaminhada para a fila para ser consumida novamente. Se false, a mensagem será descartada ou enviada para a DLX (se configurada).
                    );
                }
            });
        } catch (AMQPTimeoutException $amqpTimeoutErr) {

            $this->error($amqpTimeoutErr->getMessage());
            logger()->error("Failed to consume messages from the Message Broker.", [
                "err" => $amqpTimeoutErr->getMessage(),
            ]);
        } catch (Throwable $err) {

            $this->error($err->getMessage());
            logger()->error("Failed to consume messages from the Message Broker.", [
                "err" => $err->getMessage(),
            ]);
        }
    }
}
