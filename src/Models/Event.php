<?php

declare(strict_types=1);

namespace Daikazu\Trackerjack\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $array)
 */
class Event extends Model
{
    protected $table = 'trackerjack_events';

    protected $fillable = [
        'visitor_id',
        'event_name',
        'payload',
        'user_id',
        'email',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'visitor_id' => 'string',
        'payload'    => 'array',
        'user_id'    => 'integer',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class, 'visitor_id', 'visitor_id');
    }
}
