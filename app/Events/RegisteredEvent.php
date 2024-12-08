<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class RegisteredEvent
{
    use Dispatchable;

    public function __construct(
        public readonly User $user
    ) {
    }
}
