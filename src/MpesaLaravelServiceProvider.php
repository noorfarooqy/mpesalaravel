<?php

namespace Noorfarooqy\MpesaLaravel;

use Illuminate\Support\ServiceProvider;
use Noorfarooqy\MpesaLaravel\Middleware\MpesaXmlParser;

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

        $router = $this->app['router'];
        $router->aliasMiddleware('xml', MpesaXmlParser::class);

        $this->loadRoutesFrom(__DIR__. '/../routes/api.php');
    }

    public function register()
    {
    }
}
