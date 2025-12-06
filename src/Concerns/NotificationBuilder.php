<?php

namespace Doppar\Notifier\Concerns;

use Doppar\Notifier\Contracts\Notification;

class NotificationBuilder
{
    /**
     * The entity that will receive the notification
     *
     * @var mixed
     */
    protected $notifiable;

    /**
     * Array of channels to send the notification through
     *
     * @var array|null
     */
    protected ?array $channels = null;

    /**
     * Delay in seconds before sending the notification
     *
     * @var int
     */
    protected int $delay = 0;

    public function __construct($notifiable)
    {
        $this->notifiable = $notifiable;
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
     * Delay notification delivery
     *
     * @param int $seconds
     * @return self
     */
    public function after(int $seconds): self
    {
        $this->delay = $seconds;

        return $this;
    }

    /**
     * Send the notification
     *
     * @param Notification $notification
     * @return mixed
     */
    public function send(Notification $notification): mixed
    {
        $pipeline = new NotificationPipeline($this->notifiable, $notification);

        if ($this->channels) {
            $pipeline->via($this->channels);
        }

        if ($this->delay > 0) {
            $pipeline->delay($this->delay);
        }

        return $pipeline->send();
    }

    /**
     * Send notification immediately
     *
     * @param Notification $notification
     * @return void
     */
    public function sendNow(Notification $notification): void
    {
        $pipeline = new NotificationPipeline($this->notifiable, $notification);

        if ($this->channels) {
            $pipeline->via($this->channels);
        }

        $pipeline->immediate();
    }
}