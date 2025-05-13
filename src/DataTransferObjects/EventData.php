<?php

declare(strict_types=1);

namespace Daikazu\Trackerjack\DataTransferObjects;

use Illuminate\Support\Carbon;

final readonly class EventData
{
    public function __construct(
        public string $visitorId,
        public string $eventName,
        public ?array $payload,
        public ?int $userId,
        public ?string $email,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            visitorId: $data['visitor_id'],
            eventName: $data['event_name'],
            payload: $data['payload'] ?? null,
            userId: $data['user_id'] ?? null,
            email: $data['email'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'visitor_id' => $this->visitorId,
            'event_name' => $this->eventName,
            'payload'    => $this->payload,
            'user_id'    => $this->userId,
            'email'      => $this->email,
            'created_at' => $this->createdAt?->toDateTimeString(),
            'updated_at' => $this->updatedAt?->toDateTimeString(),
        ];
    }
}
