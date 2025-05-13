<?php

declare(strict_types=1);

namespace Daikazu\Trackerjack\Http\Controllers;

use Daikazu\Trackerjack\DataTransferObjects\EventData;
use Daikazu\Trackerjack\Jobs\ProcessEvent;
use Daikazu\Trackerjack\Services\VisitorIdentifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class EventController extends Controller
{
    protected VisitorIdentifier $visitorIdentifier;

    public function __construct(VisitorIdentifier $visitorIdentifier)
    {
        $this->visitorIdentifier = $visitorIdentifier;
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'payload' => 'nullable|array',
        ]);

        $visitorId = $this->visitorIdentifier->getVisitorId($request);

        if (! $visitorId) {
            return response()->json(['error' => 'Visitor ID not found'], 400);
        }

        $eventData = new EventData(
            visitorId: $visitorId,
            eventName: $validated['event_name'],
            payload: $validated['payload'] ?? null,
            userId: auth()->id(),
            email: auth()->user()?->email,
        );

        ProcessEvent::dispatch($eventData);

        return response()->json(['message' => 'Event tracked successfully']);
    }
}
