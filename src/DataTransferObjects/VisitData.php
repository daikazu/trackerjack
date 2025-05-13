<?php

declare(strict_types=1);

namespace Daikazu\Trackerjack\DataTransferObjects;

use Illuminate\Support\Carbon;

final readonly class VisitData
{
    public function __construct(
        public string $visitorId,
        public string $url,
        public ?string $referrer,
        public ?string $utmSource,
        public ?string $utmMedium,
        public ?string $utmCampaign,
        public ?string $utmTerm,
        public ?string $utmContent,
        public ?string $gclid,
        public ?string $ipAddress,
        public ?string $userAgent,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            visitorId: $data['visitor_id'],
            url: $data['url'],
            referrer: $data['referrer'] ?? null,
            utmSource: $data['utm_source'] ?? null,
            utmMedium: $data['utm_medium'] ?? null,
            utmCampaign: $data['utm_campaign'] ?? null,
            utmTerm: $data['utm_term'] ?? null,
            utmContent: $data['utm_content'] ?? null,
            gclid: $data['gclid'] ?? null,
            ipAddress: $data['ip_address'] ?? null,
            userAgent: $data['user_agent'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'visitor_id'   => $this->visitorId,
            'url'          => $this->url,
            'referrer'     => $this->referrer,
            'utm_source'   => $this->utmSource,
            'utm_medium'   => $this->utmMedium,
            'utm_campaign' => $this->utmCampaign,
            'utm_term'     => $this->utmTerm,
            'utm_content'  => $this->utmContent,
            'gclid'        => $this->gclid,
            'ip_address'   => $this->ipAddress,
            'user_agent'   => $this->userAgent,
            'created_at'   => $this->createdAt?->toDateTimeString(),
            'updated_at'   => $this->updatedAt?->toDateTimeString(),
        ];
    }
}
