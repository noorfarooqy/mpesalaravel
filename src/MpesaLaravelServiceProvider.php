<?php

namespace Noorfarooqy\MpesaLaravel;

use Illuminate\Support\ServiceProvider;

class MpesaLaravelServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/mpesalaravel.php' => config_path('mpesalaravel.php'),
        ], 'mpesa-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations/'),
        ], 'mpesa-database');
    }

    public function register()
    {
    }
}
