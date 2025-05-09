<?php

declare(strict_types=1);

namespace Daikazu\Trackerjack\Commands;

use Daikazu\Trackerjack\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PruneEventsCommand extends Command
{
    protected $signature = 'trackerjack:prune-events';

    protected $description = 'Prune old event records';

    public function handle(): int
    {
        $cutoff = Carbon::now()->subMinutes((int) config('trackerjack.cleanup.events_older_than', 60 * 24 * 90));

        $deleted = Event::where('created_at', '<', $cutoff)->delete();

        $this->info("Deleted {$deleted} old event records.");

        return self::SUCCESS;
    }
}
