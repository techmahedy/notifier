<?php

namespace Doppar\Notifier;

use Phaseolies\Support\Facades\Log;
use Doppar\Queue\Job;
use Doppar\Queue\InteractsWithModelSerialization;
use Doppar\Queue\Dispatchable;
use Doppar\Queue\Attributes\Queueable;
use Doppar\Notifier\Models\DatabaseNotification;
use Doppar\Notifier\Contracts\Notification;

#[Queueable(tries: 3, retryAfter: 60, onQueue: 'notifications')]
class NotificationDispatcher extends Job
{
    use InteractsWithModelSerialization, Dispatchable;

    /**
     * Initialize the dispatcher with a notifiable, notification, and optional channels
     *
     * @param mixed $notifiable
     * @param Notification $notification
     * @param array $channels
     */
    public function __construct(
        public mixed $notifiable,
        public Notification $notification,
        public array $channels = []
    ) {}

    /**
     * Sends the notification across all specified channels
     *
     * @return void
     */
    public function handle(): void
    {
        if (!$this->notification->shouldSend($this->notifiable)) {
            return;
        }

        $manager = app(NotificationManager::class);

        if (in_array('database', $this->channels)) {
            $this->storeNotification();
        }

        foreach ($this->channels as $channel) {
            try {
                if ($channel === 'database') {
                    continue;
                }

                $driver = $manager->channel($channel);
                $driver->send($this->notifiable, $this->notification);
            } catch (\Throwable $e) {
                error("Notification failed on channel {$channel}: " . $e->getMessage());
                throw $e;
            }
        }
    }

    /**
     * Store the notification in the database
     *
     * @return int|null
     */
    protected function storeNotification(): ?int
    {
        $notifiableType = get_class($this->notifiable);
        $notifiableId = method_exists($this->notifiable, 'getKey')
            ? $this->notifiable->getKey()
            : $this->notifiable->id;

        $notification = DatabaseNotification::create([
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $notifiableId,
            'type' => get_class($this->notification),
            'data' => json_encode($this->notification->content($this->notifiable)),
            'metadata' => json_encode($this->notification->metadata()),
            'read_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $notification->id ?? null;
    }

    /**
     * Logs the exception message when dispatch fails
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Notification dispatch failed: " . $exception->getMessage());
    }
}
