<?php

declare(strict_types=1);

namespace Daikazu\Trackerjack\Jobs;

use Daikazu\Trackerjack\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $visitorId,
        protected string $eventName,
        protected array $payload,
        protected ?int $userId = null,
        protected ?string $email = null,
    ) {
    }

    public function handle(): void
    {
        Event::create([
            'visitor_id' => $this->visitorId,
            'event_name' => $this->eventName,
            'payload' => $this->payload,
            'user_id' => $this->userId,
            'email' => $this->email,
        ]);
    }
} 