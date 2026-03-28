<?php

namespace App\Providers;

use App\Infrastructure\Queue\RabbitMQ;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RabbitMQ::class, fn() => new RabbitMQ());
    }

    public function boot(): void {}
}
