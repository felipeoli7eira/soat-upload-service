<?php

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Queue\RabbitMQ;
use Illuminate\Console\Command;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use Throwable;

class MessageBrokerSetup extends Command
{
    protected $signature = 'queue:setup';
    protected $description = 'Starts the necessary configurations for the Message Broker that the application is using (initially designed to use RabbitMQ).';

    public function handle()
    {
        try {
            $this->info("Message Broker started.");

            $messageBroker = new RabbitMQ();
            $messageBroker->setup();
        } catch (AMQPTimeoutException $amqpTimeoutErr) {

            $this->error($amqpTimeoutErr->getMessage());
            logger()->error("Failed to start Message Broker.", [
                "err" => $amqpTimeoutErr->getMessage(),
            ]);
        } catch (Throwable $err) {

            $this->error($err->getMessage());
            logger()->error("Message Broker failed to start.", [
                "err" => $err->getMessage(),
            ]);
        }
    }
}
