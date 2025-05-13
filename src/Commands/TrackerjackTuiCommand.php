<?php

declare(strict_types=1);

namespace Daikazu\Trackerjack\Commands;

use Carbon\Carbon;
use Daikazu\Trackerjack\Models\Event;
use Daikazu\Trackerjack\Models\Visit;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrackerjackTuiCommand extends Command
{
    protected $signature = 'trackerjack:tui {--days=7 : Number of days to analyze} {--visitor= : Specific visitor ID to analyze}';

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
                    'Recent Activity',
                    'Visitor Analysis',
                    'Event Statistics',
                    'UTM Attribution',
                    'Visitor Journey',
                    'Exit',
                ]
            );

            match ($choice) {
                'Recent Activity' => $this->showRecentActivity(),
                'Visitor Analysis' => $this->showVisitorAnalysis(),
                'Event Statistics' => $this->showEventStatistics(),
                'UTM Attribution' => $this->showUtmAttribution(),
                'Visitor Journey' => $this->showVisitorJourney(),
                'Exit' => exit(0),
            };
        }
    }

    protected function showRecentActivity(): void
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $this->info("\n📊 Activity Overview (Last {$days} days)");
        $this->newLine();

        // Get hourly activity
        $hourlyActivity = Visit::where('created_at', '>=', $cutoff)
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00") as hour'),
                DB::raw('count(*) as count')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $this->showActivityChart($hourlyActivity, 'Hourly Activity');

        // Get top pages
        $topPages = Visit::where('created_at', '>=', $cutoff)
            ->select('url', DB::raw('count(*) as count'))
            ->groupBy('url')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        $this->info("\nTop Pages:");
        $this->table(
            ['URL', 'Visits'],
            $topPages->map(fn ($page) => [
                Str::limit($page->url, 50),
                $page->count,
            ])
        );
    }

    protected function showVisitorAnalysis(): void
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $this->info("\n👥 Visitor Analysis (Last {$days} days)");
        $this->newLine();

        // Get visitor frequency using a subquery
        $visitorFrequency = DB::table(function ($query) use ($cutoff) {
            $query->select('visitor_id')
                ->selectRaw('count(*) as visit_count')
                ->from('trackerjack_visits')
                ->where('created_at', '>=', $cutoff)
                ->groupBy('visitor_id');
        }, 'visitor_counts')
            ->selectRaw('
                CASE
                    WHEN visit_count = 1 THEN "One-time"
                    WHEN visit_count <= 3 THEN "Occasional"
                    WHEN visit_count <= 10 THEN "Regular"
                    ELSE "Frequent"
                END as frequency
            ')
            ->selectRaw('count(*) as visitor_count')
            ->groupBy('frequency')
            ->orderByRaw('
                CASE frequency
                    WHEN "One-time" THEN 1
                    WHEN "Occasional" THEN 2
                    WHEN "Regular" THEN 3
                    WHEN "Frequent" THEN 4
                END
            ')
            ->get();

        $this->info('Visitor Frequency Distribution:');
        $this->table(
            ['Frequency', 'Visitors', 'Percentage'],
            $visitorFrequency->map(function ($freq) use ($visitorFrequency) {
                $total = $visitorFrequency->sum('visitor_count');
                $percentage = ($freq->visitor_count / $total) * 100;

                return [
                    $freq->frequency,
                    $freq->visitor_count,
                    number_format($percentage, 1) . '%',
                ];
            })
        );

        // Get returning visitors
        $returningVisitors = Visit::where('created_at', '>=', $cutoff)
            ->select('visitor_id')
            ->groupBy('visitor_id')
            ->having(DB::raw('count(*)'), '>', 1)
            ->count();

        $totalVisitors = Visit::where('created_at', '>=', $cutoff)
            ->distinct('visitor_id')
            ->count();

        $this->info("\nReturning Visitors: {$returningVisitors} out of {$totalVisitors} (" .
            number_format(($returningVisitors / $totalVisitors) * 100, 1) . '%)');

        // Show visitor retention
        $this->showVisitorRetention($cutoff);
    }

    protected function showVisitorRetention(Carbon $cutoff): void
    {
        $this->info("\nVisitor Retention Analysis:");

        // Get daily new visitors
        $dailyNewVisitors = Visit::where('created_at', '>=', $cutoff)
            ->select(DB::raw('DATE(created_at) as date'))
            ->selectRaw('count(distinct visitor_id) as new_visitors')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        if ($dailyNewVisitors->isEmpty()) {
            $this->line('No visitor data available for the selected period.');

            return;
        }

        $this->info("\nDaily New Visitors:");
        $this->table(
            ['Date', 'New Visitors'],
            $dailyNewVisitors->map(fn ($day) => [
                $day->date,
                $day->new_visitors,
            ])
        );

        // Calculate average daily visitors
        $avgDailyVisitors = $dailyNewVisitors->avg('new_visitors');
        $this->info("\nAverage Daily Visitors: " . number_format($avgDailyVisitors, 1));

        // Show visitor activity by hour
        $hourlyActivity = Visit::where('created_at', '>=', $cutoff)
            ->select(DB::raw('HOUR(created_at) as hour'))
            ->selectRaw('count(distinct visitor_id) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        if ($hourlyActivity->isNotEmpty()) {
            $this->info("\nHourly Visitor Distribution:");
            $this->showHourlyDistribution($hourlyActivity);
        }
    }

    protected function showHourlyDistribution(Collection $hourlyData): void
    {
        $maxCount = $hourlyData->max('count');
        $barLength = 30;

        // Create a 24-hour array with zero counts
        $hours = array_fill(0, 24, 0);

        // Fill in the actual counts
        foreach ($hourlyData as $data) {
            $hours[$data->hour] = $data->count;
        }

        // Display the distribution
        foreach ($hours as $hour => $count) {
            $bar = str_repeat('█', (int) (($count / $maxCount) * $barLength));
            $hourLabel = sprintf('%02d:00', $hour);
            $this->line(sprintf(
                '%-8s | %-30s | %d visitors',
                $hourLabel,
                $bar,
                $count
            ));
        }

        // Show peak hours
        $peakHours = $hourlyData->sortByDesc('count')->take(3);
        if ($peakHours->isNotEmpty()) {
            $this->newLine();
            $this->info('Peak Hours:');
            foreach ($peakHours as $peak) {
                $hourLabel = sprintf('%02d:00', $peak->hour);
                $this->line("  • {$hourLabel}: {$peak->count} visitors");
            }
        }
    }

    protected function showEventStatistics(): void
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $this->info("\n📈 Event Statistics (Last {$days} days)");
        $this->newLine();

        $events = Event::where('created_at', '>=', $cutoff)
            ->select('event_name', DB::raw('count(*) as count'))
            ->groupBy('event_name')
            ->orderByDesc('count')
            ->get();

        $this->table(
            ['Event Name', 'Count', 'Percentage'],
            $events->map(function ($event) use ($events) {
                $total = $events->sum('count');
                $percentage = ($event->count / $total) * 100;

                return [
                    $event->event_name,
                    $event->count,
                    number_format($percentage, 1) . '%',
                ];
            })
        );

        $this->showEventChart($events);

        // Show event trends
        $eventTrends = Event::where('created_at', '>=', $cutoff)
            ->select(
                'event_name',
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->groupBy('event_name', 'date')
            ->orderBy('date')
            ->get()
            ->groupBy('event_name');

        $this->info("\nEvent Trends:");
        foreach ($eventTrends as $eventName => $trends) {
            $this->info("\n{$eventName}:");
            $this->showTrendChart($trends);
        }
    }

    protected function showUtmAttribution(): void
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $this->info("\n🎯 UTM Attribution Analysis (Last {$days} days)");
        $this->newLine();

        $utmStats = Visit::where('created_at', '>=', $cutoff)
            ->whereNotNull('utm_source')
            ->select('utm_source', 'utm_medium', DB::raw('count(*) as count'))
            ->groupBy('utm_source', 'utm_medium')
            ->orderByDesc('count')
            ->get();

        $this->table(
            ['Source', 'Medium', 'Visits', 'Percentage'],
            $utmStats->map(function ($stat) use ($utmStats) {
                $total = $utmStats->sum('count');
                $percentage = ($stat->count / $total) * 100;

                return [
                    $stat->utm_source,
                    $stat->utm_medium ?? 'N/A',
                    $stat->count,
                    number_format($percentage, 1) . '%',
                ];
            })
        );

        $this->showUtmChart($utmStats);
    }

    protected function showVisitorJourney(): void
    {
        $visitorId = $this->option('visitor');
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        if ($visitorId) {
            $this->analyzeSpecificVisitor($visitorId, $cutoff);
        } else {
            $this->showVisitorSearch($cutoff);
        }
    }

    protected function showVisitorSearch(Carbon $cutoff): void
    {
        $this->info("\n🔍 Visitor Search");
        $this->newLine();

        $choice = $this->choice(
            'How would you like to search for visitors?',
            [
                'Search by ID',
                'Show frequent visitors',
                'Show recent visitors',
                'Show visitors with most events',
                'Back to main menu',
            ]
        );

        match ($choice) {
            'Search by ID' => $this->searchVisitorById($cutoff),
            'Show frequent visitors' => $this->showFrequentVisitors($cutoff),
            'Show recent visitors' => $this->showRecentVisitors($cutoff),
            'Show visitors with most events' => $this->showVisitorsWithMostEvents($cutoff),
            'Back to main menu' => null,
        };
    }

    protected function searchVisitorById(Carbon $cutoff): void
    {
        $searchId = $this->ask('Enter visitor ID (or partial ID):');

        $visitors = Visit::where('visitor_id', 'like', "%{$searchId}%")
            ->where('created_at', '>=', $cutoff)
            ->select('visitor_id')
            ->distinct()
            ->take(10)
            ->get();

        if ($visitors->isEmpty()) {
            $this->error("No visitors found matching '{$searchId}'");

            return;
        }

        if ($visitors->count() === 1) {
            $this->analyzeSpecificVisitor($visitors->first()->visitor_id, $cutoff);

            return;
        }

        $this->info("\nFound multiple visitors. Please select one:");
        $choices = $visitors->map(fn ($v) => $v->visitor_id)->toArray();
        $selectedId = $this->choice('Select visitor:', $choices);

        $this->analyzeSpecificVisitor($selectedId, $cutoff);
    }

    protected function showFrequentVisitors(Carbon $cutoff): void
    {
        $visitors = Visit::where('created_at', '>=', $cutoff)
            ->select('visitor_id')
            ->selectRaw('count(*) as visit_count')
            ->groupBy('visitor_id')
            ->orderByDesc('visit_count')
            ->take(10)
            ->get();

        if ($visitors->isEmpty()) {
            $this->error('No visitor data available for the selected period.');

            return;
        }

        $this->info("\nMost Frequent Visitors:");
        $this->table(
            ['Visitor ID', 'Visit Count', 'Last Visit'],
            $visitors->map(function ($visitor) {
                $lastVisit = Visit::where('visitor_id', $visitor->visitor_id)
                    ->latest()
                    ->first();

                return [
                    Str::limit($visitor->visitor_id, 8),
                    $visitor->visit_count,
                    $lastVisit->created_at->diffForHumans(),
                ];
            })
        );

        $this->promptForVisitorAnalysis($visitors, $cutoff);
    }

    protected function showRecentVisitors(Carbon $cutoff): void
    {
        $visitors = Visit::where('created_at', '>=', $cutoff)
            ->select('visitor_id')
            ->selectRaw('max(created_at) as last_visit')
            ->groupBy('visitor_id')
            ->orderByDesc('last_visit')
            ->take(10)
            ->get();

        if ($visitors->isEmpty()) {
            $this->error('No visitor data available for the selected period.');

            return;
        }

        $this->info("\nMost Recent Visitors:");
        $this->table(
            ['Visitor ID', 'Last Visit', 'Visit Count'],
            $visitors->map(function ($visitor) {
                $visitCount = Visit::where('visitor_id', $visitor->visitor_id)->count();

                return [
                    Str::limit($visitor->visitor_id, 8),
                    Carbon::parse($visitor->last_visit)->diffForHumans(),
                    $visitCount,
                ];
            })
        );

        $this->promptForVisitorAnalysis($visitors, $cutoff);
    }

    protected function showVisitorsWithMostEvents(Carbon $cutoff): void
    {
        $visitors = Event::where('created_at', '>=', $cutoff)
            ->select('visitor_id')
            ->selectRaw('count(*) as event_count')
            ->groupBy('visitor_id')
            ->orderByDesc('event_count')
            ->take(10)
            ->get();

        if ($visitors->isEmpty()) {
            $this->error('No visitor data available for the selected period.');

            return;
        }

        $this->info("\nVisitors with Most Events:");
        $this->table(
            ['Visitor ID', 'Event Count', 'Last Event'],
            $visitors->map(function ($visitor) {
                $lastEvent = Event::where('visitor_id', $visitor->visitor_id)
                    ->latest()
                    ->first();

                return [
                    Str::limit($visitor->visitor_id, 8),
                    $visitor->event_count,
                    $lastEvent->created_at->diffForHumans(),
                ];
            })
        );

        $this->promptForVisitorAnalysis($visitors, $cutoff);
    }

    protected function promptForVisitorAnalysis(Collection $visitors, Carbon $cutoff): void
    {
        if ($this->confirm('Would you like to analyze a specific visitor?')) {
            $choices = $visitors->map(fn ($v) => $v->visitor_id)->toArray();
            $selectedId = $this->choice('Select visitor:', $choices);
            $this->analyzeSpecificVisitor($selectedId, $cutoff);
        }
    }

    protected function analyzeSpecificVisitor(string $visitorId, Carbon $cutoff): void
    {
        $visits = Visit::with('events')
            ->where('visitor_id', $visitorId)
            ->where('created_at', '>=', $cutoff)
            ->orderBy('created_at')
            ->get();

        if ($visits->isEmpty()) {
            $this->error("No visits found for visitor {$visitorId}");

            return;
        }

        $this->info("\n🛣️  Visitor Journey Analysis");
        $this->info('Visitor ID: ' . Str::limit($visitorId, 8));
        $this->newLine();

        // Show visit summary
        $this->showVisitSummary($visits);

        // Show detailed journey
        $this->showDetailedJourney($visits);

        // Show event analysis
        $this->showEventAnalysis($visits);

        // Show time analysis
        $this->showTimeAnalysis($visits);
    }

    protected function showVisitSummary(Collection $visits): void
    {
        $this->info('Visit Summary:');
        $this->line('Total Visits: ' . $visits->count());
        $this->line('First Visit: ' . $visits->first()->created_at->format('Y-m-d H:i:s'));
        $this->line('Last Visit: ' . $visits->last()->created_at->format('Y-m-d H:i:s'));

        $totalEvents = $visits->sum(fn ($visit) => $visit->events->count());
        $this->line('Total Events: ' . $totalEvents);
        $this->newLine();
    }

    protected function showDetailedJourney(Collection $visits): void
    {
        $this->info('Detailed Journey:');

        // Sort visits chronologically
        $sortedVisits = $visits->sortBy('created_at');

        foreach ($sortedVisits as $index => $visit) {
            $this->info("\nVisit at " . $visit->created_at->format('Y-m-d H:i:s'));
            $this->line('URL: ' . $visit->url);

            // Get events that occurred during this visit
            $visitEvents = $visit->events->filter(function ($event) use ($sortedVisits, $index) {
                // If this is the last visit, include all its events
                if ($index === $sortedVisits->count() - 1) {
                    return true;
                }

                // For other visits, only include events that occurred before the next visit
                $nextVisit = $sortedVisits[$index + 1];

                return $event->created_at < $nextVisit->created_at;
            })->sortBy('created_at');

            if ($visitEvents->isNotEmpty()) {
                $this->line('Events during this visit:');
                foreach ($visitEvents as $event) {
                    $this->line("  • {$event->event_name} at " . $event->created_at->format('H:i:s'));
                }
            }

            // Show time until next visit if not the last visit
            if ($index < $sortedVisits->count() - 1) {
                $nextVisit = $sortedVisits[$index + 1];
                $timeUntilNext = $visit->created_at->diffForHumans($nextVisit->created_at, ['parts' => 2]);
                $this->line('Time until next visit: ' . $timeUntilNext);
            }
        }

        $this->newLine();
    }

    protected function showEventAnalysis(Collection $visits): void
    {
        $this->info('Event Analysis:');

        // Get all events and group by name and timestamp to ensure uniqueness
        $eventTypes = $visits->flatMap(fn ($visit) => $visit->events)
            ->unique(function ($event) {
                return $event->event_name . '_' . $event->created_at->timestamp;
            })
            ->groupBy('event_name')
            ->map(fn ($events) => $events->count());

        if ($eventTypes->isNotEmpty()) {
            $this->line("\nEvent Types:");
            foreach ($eventTypes as $type => $count) {
                $this->line("  • {$type}: {$count} times");
            }
        }

        // Show most common event sequences
        $this->showEventSequences($visits);
    }

    protected function showEventSequences(Collection $visits): void
    {
        $sequences = [];
        $sortedVisits = $visits->sortBy('created_at');

        foreach ($sortedVisits as $index => $visit) {
            // Get events that occurred during this visit
            $visitEvents = $visit->events->filter(function ($event) use ($sortedVisits, $index) {
                if ($index === $sortedVisits->count() - 1) {
                    return true;
                }
                $nextVisit = $sortedVisits[$index + 1];

                return $event->created_at < $nextVisit->created_at;
            })->sortBy('created_at');

            if ($visitEvents->count() >= 2) {
                $eventNames = $visitEvents->pluck('event_name')->toArray();
                for ($i = 0; $i < count($eventNames) - 1; $i++) {
                    $sequence = $eventNames[$i] . ' → ' . $eventNames[$i + 1];
                    $sequences[$sequence] = ($sequences[$sequence] ?? 0) + 1;
                }
            }
        }

        if (! empty($sequences)) {
            $this->line("\nCommon Event Sequences:");
            arsort($sequences);
            foreach (array_slice($sequences, 0, 5) as $sequence => $count) {
                $this->line("  • {$sequence}: {$count} times");
            }
        }
    }

    protected function showTimeAnalysis(Collection $visits): void
    {
        $this->info("\nTime Analysis:");

        // Calculate average time between visits
        $intervals = [];
        for ($i = 1; $i < $visits->count(); $i++) {
            $interval = $visits[$i]->created_at->diffInHours($visits[$i - 1]->created_at);
            $intervals[] = $interval;
        }

        if (! empty($intervals)) {
            $avgInterval = array_sum($intervals) / count($intervals);
            $this->line('Average time between visits: ' . number_format($avgInterval, 1) . ' hours');
        }

        // Show visit times
        $this->line("\nVisit Times:");
        $visitHours = $visits->map(fn ($visit) => $visit->created_at->format('H:i'));
        $this->line('  • ' . $visitHours->join(', '));
    }

    protected function showActivityChart(Collection $data, string $title): void
    {
        $this->info("\n{$title}:");

        $maxCount = $data->max('count');
        $barLength = 30;

        foreach ($data as $point) {
            $bar = str_repeat('█', (int) (($point->count / $maxCount) * $barLength));
            $this->line(sprintf(
                '%-20s | %-30s | %d',
                $point->hour ?? $point->date,
                $bar,
                $point->count
            ));
        }
    }

    protected function showTrendChart(Collection $trends): void
    {
        $maxCount = $trends->max('count');
        $barLength = 30;

        foreach ($trends as $trend) {
            $bar = str_repeat('█', (int) (($trend->count / $maxCount) * $barLength));
            $this->line(sprintf(
                '%-12s | %-30s | %d',
                $trend->date,
                $bar,
                $trend->count
            ));
        }
    }

    protected function showEventChart(Collection $events): void
    {
        $this->newLine();
        $this->info('Event Distribution');

        $maxCount = $events->max('count');
        $barLength = 30;

        foreach ($events as $event) {
            $bar = str_repeat('█', (int) (($event->count / $maxCount) * $barLength));
            $this->line(sprintf(
                '%-20s | %-30s | %d',
                Str::limit($event->event_name, 20),
                $bar,
                $event->count
            ));
        }
    }

    protected function showUtmChart(Collection $utmStats): void
    {
        $this->newLine();
        $this->info('UTM Source Distribution');

        $maxCount = $utmStats->max('count');
        $barLength = 30;

        foreach ($utmStats as $stat) {
            $bar = str_repeat('█', (int) (($stat->count / $maxCount) * $barLength));
            $this->line(sprintf(
                '%-15s | %-30s | %d visits',
                Str::limit($stat->utm_source, 15),
                $bar,
                $stat->count
            ));
        }
    }
}
