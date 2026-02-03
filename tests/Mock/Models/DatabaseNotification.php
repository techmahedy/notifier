<?php

namespace Doppar\Notifier\Tests\Mock\Models;

use Phaseolies\Database\Entity\Model;

class DatabaseNotification extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';
    protected $connection = 'default';

    /**
     * The attributes that are mass assignable.
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
        'created_at'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timeStamps = false;
}
