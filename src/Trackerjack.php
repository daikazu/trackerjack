<?php

declare(strict_types=1);

namespace Daikazu\Trackerjack;

use Daikazu\Trackerjack\Models\Event;
use Illuminate\Support\Facades\Cookie;

class Trackerjack
{
    public function trackEvent(string $eventName, array $payload = []): void
    {
        if ($this->isEventAllowed($eventName)) {
            Event::create([
                'visitor_id' => $this->getVisitorId(),
                'event_name' => $eventName,
                'payload' => $payload,
                'user_id' => auth()->id(),
                'email' => auth()->user()?->email,
            ]);
        }
    }

    public function bindToUser($user): void
    {
        $visitorId = $this->getVisitorId();

        Event::where('visitor_id', $visitorId)
            ->whereNull('user_id')
            ->update([
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
    }

    protected function isEventAllowed(string $eventName): bool
    {
        $allowedEvents = config('trackerjack.allowed_events');

        return $allowedEvents === null || in_array($eventName, (array) $allowedEvents, true);
    }

    protected function getVisitorId(): ?string
    {
        return Cookie::get(config('trackerjack.cookie.name'));
    }
}
