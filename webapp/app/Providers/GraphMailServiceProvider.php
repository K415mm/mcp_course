<?php

namespace App\Providers;

use App\Mail\Transport\GraphTransport;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;

class GraphMailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->app->make(MailManager::class)->extend('graph', function (array $config) {
            return new GraphTransport($config);
        });
    }
}
