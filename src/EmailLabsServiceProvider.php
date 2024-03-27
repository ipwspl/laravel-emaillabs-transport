<?php
declare(strict_types=1);

namespace Ipws\EmailLabs;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Ipws\EmailLabs\Transport\EmailLabsTransport;


class EmailLabsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerEmailLabsTransport();
    }

    protected function registerEmailLabsTransport()
    {
        Mail::extend('emaillabs', function(){
            $config = $this->app['config']->get('services.emaillabs', []);
            return new EmailLabsTransport($config);
        });
    }

    public function register()
    {
        //$this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'emaillabs');
    }
}