<?php

declare(strict_types=1);

namespace Daikazu\Trackerjack\Jobs;

use Daikazu\Trackerjack\Models\Visit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ProcessVisitBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Collection $visits
    ) {
    }

    public function handle(): void
    {
        Visit::insert($this->visits->map(fn ($visit) => [
            'visitor_id' => $visit['visitor_id'],
            'url' => $visit['url'],
            'referrer' => $visit['referrer'],
            'utm_source' => $visit['utm_source'],
            'utm_medium' => $visit['utm_medium'],
            'utm_campaign' => $visit['utm_campaign'],
            'utm_term' => $visit['utm_term'],
            'utm_content' => $visit['utm_content'],
            'ip_address' => $visit['ip_address'],
            'user_agent' => $visit['user_agent'],
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray());
    }
} 