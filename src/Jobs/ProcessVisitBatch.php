<?php

declare(strict_types=1);

namespace Daikazu\Trackerjack\Jobs;

use Daikazu\Trackerjack\DataTransferObjects\VisitData;
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
        $now = now();
        
        Visit::insert($this->visits->map(function (array $visit) use ($now) {
            $visitData = VisitData::fromArray($visit);
            $data = $visitData->toArray();
            
            // Ensure timestamps are set for bulk insert
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
            
            return $data;
        })->toArray());
    }
} 