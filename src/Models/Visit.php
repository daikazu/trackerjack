<?php

declare(strict_types=1);

namespace Daikazu\Trackerjack\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visit extends Model
{
    protected $table = 'trackerjack_visits';

    protected $fillable = [
        'visitor_id',
        'url',
        'referrer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'ip_address',
        'user_agent',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'visitor_id', 'visitor_id');
    }

    protected function casts(): array
    {
        return [
            'visitor_id' => 'string',
        ];
    }
}
