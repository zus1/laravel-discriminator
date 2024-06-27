<?php

namespace Zus1\Discriminator\Providers;

use Illuminate\Support\ServiceProvider;

class DiscriminatorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');

        $this->publishesMigrations([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ]);


    }
}