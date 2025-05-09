<?php

declare(strict_types=1);

namespace Daikazu\Trackerjack\Commands;

use Daikazu\Trackerjack\Models\Visit;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PruneVisitsCommand extends Command
{
    protected $signature = 'trackerjack:prune-visits';

    protected $description = 'Prune old visit records';

    public function handle(): int
    {
        $cutoff = Carbon::now()->subMinutes((int) config('trackerjack.cleanup.visits_older_than', 60 * 24 * 30));

        $deleted = Visit::where('created_at', '<', $cutoff)->delete();

        $this->info("Deleted {$deleted} old visit records.");

        return self::SUCCESS;
    }
}
