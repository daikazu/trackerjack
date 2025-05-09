<?php

declare(strict_types=1);

namespace Daikazu\Trackerjack\Commands;

use Illuminate\Console\Command;
use Daikazu\Trackerjack\Models\Visit;
use Daikazu\Trackerjack\Models\Event;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TrackerjackTuiCommand extends Command
{
    protected $signature = 'trackerjack:tui';

    protected $description = 'View tracking data in the terminal';

    public function handle(): int
    {
        $this->showMainMenu();

        return self::SUCCESS;
    }

    protected function showMainMenu(): void
    {
        while (true) {
            $choice = $this->choice(
                'What would you like to view?',
                [
                    'Recent Visits',
                    'Event Statistics',
                    'UTM Attribution',
                    'Exit',
                ]
            );

            match ($choice) {
                'Recent Visits' => $this->showRecentVisits(),
                'Event Statistics' => $this->showEventStatistics(),
                'UTM Attribution' => $this->showUtmAttribution(),
                'Exit' => exit(0),
            };
        }
    }

    protected function showRecentVisits(): void
    {
        $visits = Visit::with('events')
            ->latest()
            ->take(10)
            ->get();

        $this->table(
            ['Visitor ID', 'URL', 'UTM Source', 'Events'],
            $visits->map(fn (Visit $visit) => [
                $visit->visitor_id,
                $visit->url,
                $visit->utm_source ?? 'N/A',
                $visit->events->count(),
            ])
        );
    }

    protected function showEventStatistics(): void
    {
        $events = Event::select('event_name', DB::raw('count(*) as count'))
            ->groupBy('event_name')
            ->orderByDesc('count')
            ->get();

        $this->table(
            ['Event Name', 'Count'],
            $events->map(fn ($event) => [
                $event->event_name,
                $event->count,
            ])
        );
    }

    protected function showUtmAttribution(): void
    {
        $utmStats = Visit::select('utm_source', 'utm_medium', DB::raw('count(*) as count'))
            ->whereNotNull('utm_source')
            ->groupBy('utm_source', 'utm_medium')
            ->orderByDesc('count')
            ->get();

        $this->table(
            ['Source', 'Medium', 'Visits'],
            $utmStats->map(fn ($stat) => [
                $stat->utm_source,
                $stat->utm_medium ?? 'N/A',
                $stat->count,
            ])
        );
    }
} 