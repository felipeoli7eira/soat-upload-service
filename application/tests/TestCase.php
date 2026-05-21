<?php

namespace Tests;

use App\Infrastructure\Queue\RabbitMQ;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected ?string $authToken = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mock(RabbitMQ::class);
    }
}
