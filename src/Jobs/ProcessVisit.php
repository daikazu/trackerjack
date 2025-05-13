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

class ProcessVisit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected VisitData $visitData
    ) {
    }

    public function handle(): void
    {
        Visit::create($this->visitData->toArray());
    }
} 