<?php

namespace Doppar\Notifier\Contracts;

use Doppar\Queue\InteractsWithModelSerialization;

abstract class Notification
{
    use InteractsWithModelSerialization;

    /**
     * Define the notification channels
     *
     * @param mixed $notifiable
     * @return array
     */
    abstract public function channels($notifiable): array;

    /**
     * Define the notification content
     *
     * @param mixed $notifiable
     * @return array
     */
    abstract public function content($notifiable): array;

    /**
     * Get notification metadata
     *
     * @return array
     */
    public function metadata(): array
    {
        return [];
    }

    /**
     * Check if notification should be sent
     *
     * @param mixed $notifiable
     * @return bool
     */
    public function shouldSend($notifiable): bool
    {
        return true;
    }

    /**
     * Get the notification's delivery delay
     *
     * @return int
     */
    public function deliveryDelay(): int
    {
        return 0;
    }
}
