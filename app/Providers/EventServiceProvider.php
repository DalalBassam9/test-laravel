<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use App\Listeners\MergeGuestCartWithUserCart;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Registered;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }
    protected $listen = [
        Login::class => [
            \App\Listeners\MergeGuestCartWithUserCart::class,
        ],
    ];
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
