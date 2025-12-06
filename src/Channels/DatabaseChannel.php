<?php

namespace Doppar\Notifier\Channels;

use Doppar\Notifier\Contracts\Notification;
use Doppar\Notifier\Channels\Contracts\ChannelDriver;

class DatabaseChannel extends ChannelDriver
{
    /**
     * Send the notification through the database channel.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     * @return void
     */
    public function send($notifiable, Notification $notification): void
    {
        // Database storage is handled by NotificationDispatcher
        // This channel just marks that database storage is needed
    }
}