<?php

declare(strict_types=1);

namespace Daikazu\Trackerjack\Jobs;

use Daikazu\Trackerjack\DataTransferObjects\EventData;
use Daikazu\Trackerjack\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessEvent implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected EventData $eventData
    ) {}

    public function handle(): void
    {
        Event::create($this->eventData->toArray());
    }
}
