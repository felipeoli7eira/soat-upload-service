<?php

namespace Tests;

use App\Infrastructure\Queue\RabbitMQ;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected ?string $authToken = null;

    protected function refreshApplication(): void
    {
        parent::refreshApplication();
        $this->app->singleton(RabbitMQ::class, fn () => \Mockery::mock(RabbitMQ::class)->shouldIgnoreMissing());
    }
}
