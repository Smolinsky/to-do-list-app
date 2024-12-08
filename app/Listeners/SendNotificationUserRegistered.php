<?php

namespace App\Listeners;

use App\Events\RegisteredEvent;
use App\Notifications\UserRegisteredNotification;

class SendNotificationUserRegistered
{
    public function handle(RegisteredEvent $event): void
    {
        $event->user->notify(new UserRegisteredNotification($event->user));
    }
}
