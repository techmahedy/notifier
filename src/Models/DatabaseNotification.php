<?php

namespace Doppar\Notifier\Models;

use Phaseolies\Database\Entity\Model;

class DatabaseNotification extends Model
{
    /**
     * The database table associated with notifications
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * List of attributes that are allowed to be mass-assigned
     *
     * @var array
     */
    protected $creatable = [
        'notifiable_type',
        'notifiable_id',
        'type',
        'data',
        'metadata',
        'read_at',
        'created_at',
    ];

    /**
     * Mark notification as read
     *
     * @return bool
     */
    public function markAsRead(): bool
    {
        if ($this->read_at) {
            return true;
        }

        $this->read_at = date('Y-m-d H:i:s');

        return $this->save();
    }

    /**
     * Mark notification as unread
     *
     * @return bool
     */
    public function markAsUnread(): bool
    {
        if (!$this->read_at) {
            return true;
        }

        $this->read_at = null;

        return $this->save();
    }

    /**
     * Check if notification is read
     *
     * @return bool
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Check if notification is unread
     *
     * @return bool
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Get the notifiable entity
     *
     * @return mixed
     */
    public function notifiable()
    {
        $class = $this->notifiable_type;

        return $class::find($this->notifiable_id);
    }
}