<?php

namespace App\Providers;

use App\Events\RegisteredEvent;
use App\Listeners\SendNotificationUserRegistered;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            RegisteredEvent::class,
            SendNotificationUserRegistered::class
        );
    }
}
