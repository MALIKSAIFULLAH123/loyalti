<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('room.{room_id}', function ($user, int $roomId) {
    return [
        'id' => $roomId,
    ];
});

Broadcast::channel('user.{user_id}', function ($user) {
   return true;
});
