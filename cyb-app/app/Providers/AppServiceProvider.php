<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // enforce https for laravel. this fixes horizon dashboard not accessible via https.
        if (env('APP_HTTPS', 'false') == 'true') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
