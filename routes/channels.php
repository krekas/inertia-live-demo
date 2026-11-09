<?php

use App\Events\OrderCreated;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel(OrderCreated::CHANNEL, function (User $user): bool {
    return $user->hasVerifiedEmail();
});
