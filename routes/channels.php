<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:userapi']]);


############ Start Chats ############
Broadcast::channel('Room.Chat.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('Read.Messages.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
############ End Chats ############

//Comments

Broadcast::channel('User.Comment.Facility.{id}', function () {
    return true;
});

//Notifications
Broadcast::channel('User.Notify.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

