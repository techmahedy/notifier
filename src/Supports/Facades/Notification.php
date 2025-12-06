<?php

namespace Doppar\Notifier\Supports\Facades;

use Doppar\Notifier\Contracts\Notification as ContractsNotification;
use Doppar\Notifier\Concerns\ScheduledNotificationBuilder;
use Doppar\Notifier\Concerns\QueryNotificationBuilder;
use Doppar\Notifier\Concerns\NotificationBuilder;
use Doppar\Notifier\Concerns\BulkNotificationBuilder;

class Notification
{
    /**
     * Send notification to a single entity
     *
     * @param mixed $notifiable
     * @return NotificationBuilder
     */
    public static function to($notifiable): NotificationBuilder
    {
        return new NotificationBuilder($notifiable);
    }

    /**
     * Send notification to multiple entities
     *
     * @param iterable $notifiables
     * @return BulkNotificationBuilder
     */
    public static function toMany(iterable $notifiables): BulkNotificationBuilder
    {
        return new BulkNotificationBuilder($notifiables);
    }

    /**
     * Send notification to all users matching criteria
     *
     * @param string $modelClass
     * @return QueryNotificationBuilder
     */
    public static function toAll(string $modelClass): QueryNotificationBuilder
    {
        return new QueryNotificationBuilder($modelClass);
    }

    /**
     * Schedule a notification
     *
     * @param ContractsNotification $notification
     * @return ScheduledNotificationBuilder
     */
    public static function schedule(ContractsNotification $notification): ScheduledNotificationBuilder
    {
        return new ScheduledNotificationBuilder($notification);
    }
}
