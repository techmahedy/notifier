<?php

namespace Doppar\Notifier\Concerns;

use Doppar\Notifier\Contracts\Notification;

class BulkNotificationBuilder
{
    /**
     * Collection of notifiable entities that will receive notifications
     *
     * @var iterable
     */
    protected iterable $notifiables;

    /**
     * Optional array of channels through which notifications should be sent.
     *
     * @var array|null
     */
    protected ?array $channels = null;

    /**
     * Delay in seconds before sending the notifications.
     *
     * @var int
     */
    protected int $delay = 0;

    /**
     * Maximum number of notifications to process at a time.
     *
     * @var int
     */
    protected int $batchSize = 100;

    /**
     * Create a new BulkNotificationBuilder instance.
     * 
     * @param iterable $notifiables
     */
    public function __construct(iterable $notifiables)
    {
        $this->notifiables = $notifiables;
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
     * Set batch size for processing
     * 
     * @param int $size
     * @return self
     */
    public function batchSize(int $size): self
    {
        $this->batchSize = $size;

        return $this;
    }

    /**
     * Send notification to all recipients
     * 
     * @param Notification $notification
     * @return array
     */
    public function send(Notification $notification): array
    {
        $jobIds = [];
        $batch = [];
        $count = 0;

        foreach ($this->notifiables as $notifiable) {
            $batch[] = $notifiable;
            $count++;

            if ($count >= $this->batchSize) {
                $jobIds = array_merge($jobIds, $this->dispatchBatch($batch, $notification));
                $batch = [];
                $count = 0;
            }
        }

        if (!empty($batch)) {
            $jobIds = array_merge($jobIds, $this->dispatchBatch($batch, $notification));
        }

        return $jobIds;
    }

    /**
     * Dispatch a batch of notifications
     * 
     * @param array $batch
     * @param Notification $notification
     * @return array
     */
    protected function dispatchBatch(array $batch, Notification $notification): array
    {
        $jobIds = [];

        foreach ($batch as $notifiable) {
            $pipeline = new NotificationPipeline($notifiable, $notification);

            if ($this->channels) {
                $pipeline->via($this->channels);
            }

            if ($this->delay > 0) {
                $pipeline->delay($this->delay);
            }

            $jobIds[] = $pipeline->send();
        }

        return $jobIds;
    }
}