<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('Room.Chat.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('Read.Messages.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});



//Notification
Broadcast::channel('User.Notify.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

