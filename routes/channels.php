<?php

use Illuminate\Support\Facades\Broadcast;

/**
 * User model channel for general user-specific broadcasts
 */
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * User notification channel - only the user can listen to their own notifications
 * 
 * This channel is used for real-time notification delivery.
 * Only authenticated users can subscribe to their own notification channel.
 */
Broadcast::channel('user.{userId}', function ($user, $userId) {
    // Ensure the authenticated user can only access their own channel
    return (int) $user->id === (int) $userId ? [
        'id' => $user->id,
        'name' => $user->name,
        'username' => $user->username,
    ] : false;
});
