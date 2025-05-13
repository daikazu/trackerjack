<?php

declare(strict_types=1);

namespace Daikazu\Trackerjack\Jobs;

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
        protected string $visitorId,
        protected string $url,
        protected ?string $referrer,
        protected ?string $utmSource,
        protected ?string $utmMedium,
        protected ?string $utmCampaign,
        protected ?string $utmTerm,
        protected ?string $utmContent,
        protected ?string $ipAddress,
        protected ?string $userAgent,
    ) {
    }

    public function handle(): void
    {
        Visit::create([
            'visitor_id' => $this->visitorId,
            'url' => $this->url,
            'referrer' => $this->referrer,
            'utm_source' => $this->utmSource,
            'utm_medium' => $this->utmMedium,
            'utm_campaign' => $this->utmCampaign,
            'utm_term' => $this->utmTerm,
            'utm_content' => $this->utmContent,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
        ]);
    }
} 