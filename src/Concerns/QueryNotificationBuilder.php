<?php

namespace Doppar\Notifier\Concerns;

use Doppar\Notifier\Contracts\Notification;

class QueryNotificationBuilder
{
    /**
     * The model class to query for notifiable records.
     *
     * @var string
     */
    protected string $modelClass;

    /**
     * Array of where conditions to filter the query
     *
     * @var array
     */
    protected array $wheres = [];

    /**
     * Array of channels to send the notifications through
     *
     * @var array|null
     */
    protected ?array $channels = null;

    /**
     * Delay in seconds before sending notifications
     *
     * @var int
     */
    protected int $delay = 0;

    /**
     * Number of records to process in each chunk
     *
     * @var int
     */
    protected int $chunkSize = 100;

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    /**
     * Add where condition
     * 
     * @param string $column
     * @param mixed $value
     * @return self
     */
    public function where(string $column, $value): self
    {
        $this->wheres[] = [$column, '=', $value];

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
     * Set chunk size for processing
     * 
     * @param int $size
     * @return self
     */
    public function chunkSize(int $size): self
    {
        $this->chunkSize = $size;

        return $this;
    }

    /**
     * Send notification to all matching records
     * 
     * @param Notification $notification
     * @return int
     */
    public function send(Notification $notification): int
    {
        $query = $this->modelClass::query();

        foreach ($this->wheres as $where) {
            $query->where($where[0], $where[1], $where[2]);
        }

        $count = 0;

        $query->chunk($this->chunkSize, function ($records) use ($notification, &$count) {
            foreach ($records as $record) {
                $pipeline = new NotificationPipeline($record, $notification);

                if ($this->channels) {
                    $pipeline->via($this->channels);
                }

                if ($this->delay > 0) {
                    $pipeline->delay($this->delay);
                }

                $pipeline->send();
                $count++;
            }
        });

        return $count;
    }
}