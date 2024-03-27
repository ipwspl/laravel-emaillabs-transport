<?php
declare(strict_types=1);

namespace Ipws\EmailLabs;

use Illuminate\Support\ServiceProvider;


class EmailLabsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('emaillabs.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'emaillabs');
    }
}