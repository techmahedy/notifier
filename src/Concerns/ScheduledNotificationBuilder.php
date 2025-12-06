<?php

namespace Doppar\Notifier\Concerns;

use Doppar\Notifier\Contracts\Notification;

class ScheduledNotificationBuilder
{
    /**
     * The notification instance to be sent
     *
     * @var Notification
     */
    protected Notification $notification;

    /**
     * The recipient of the notification
     *
     * @var mixed
     */
    protected $notifiable = null;

    /**
     * Optional array of channels to send the notification through
     *
     * @var array|null
     */
    protected ?array $channels = null;

    /**
     * Timestamp at which the notification should be sent
     *
     * @var int
     */
    protected int $scheduledAt = 0;

    /**
     * Initialize the builder with a notification instance
     *
     * @param Notification $notification
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Set recipient
     * 
     * @param mixed $notifiable
     * @return self
     */
    public function to($notifiable): self
    {
        $this->notifiable = $notifiable;

        return $this;
    }

    /**
     * Send via specific channels
     * 
     * @param array $channels
     * @return self
     */
    public function via(array $channels): self
    {
        $this->channels = $channels;

        return $this;
    }

    /**
     * Schedule for specific timestamp
     * 
     * @param int $timestamp
     * @return mixed
     */
    public function at(int $timestamp): mixed
    {
        $this->scheduledAt = $timestamp;

        return $this->dispatch();
    }

    /**
     * Schedule after seconds from now
     * 
     * @param int $seconds
     * @return mixed
     */
    public function after(int $seconds): mixed
    {
        $this->scheduledAt = time() + $seconds;

        return $this->dispatch();
    }

    /**
     * Dispatch the scheduled notification
     * 
     * @return mixed
     */
    protected function dispatch(): mixed
    {
        if (!$this->notifiable) {
            throw new \RuntimeException('No recipient specified for notification.');
        }

        $delay = max(0, $this->scheduledAt - time());

        $pipeline = new NotificationPipeline($this->notifiable, $this->notification);

        if ($this->channels) {
            $pipeline->via($this->channels);
        }

        return $pipeline->delay($delay)->send();
    }
}