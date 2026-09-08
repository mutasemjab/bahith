<?php

namespace App\Providers;

use App\Contracts\AppleTransactionVerifier;
use App\Services\Apple\AppleSignedTransactionVerifier;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            AppleTransactionVerifier::class,
            AppleSignedTransactionVerifier::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::USeBootstrap();
   
    }
}
