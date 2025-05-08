<?php

namespace Daikazu\Trackerjack\Commands;

use Illuminate\Console\Command;

class TrackerjackCommand extends Command
{
    public $signature = 'trackerjack';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
