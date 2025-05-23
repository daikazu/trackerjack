<?php

declare(strict_types=1);

namespace Daikazu\Trackerjack\Http\Middleware;

use Closure;
use Daikazu\Trackerjack\Jobs\ProcessVisitBatch;
use Daikazu\Trackerjack\Services\VisitorIdentifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrackVisits
{
    protected static array $visitBuffer = [];

    protected const BATCH_SIZE = 100;

    public function __construct(protected VisitorIdentifier $visitorIdentifier) {}

    public function handle(Request $request, Closure $next): Response
    {

        $visitorId = $this->visitorIdentifier->getVisitorId($request);

        Log::info('TrackerJack: Current visitor ID', ['visitor_id' => $visitorId]);

        if ($this->shouldTrack($request)) {
            $this->queueVisit($request, $visitorId);
        }

        $response = $next($request);
        $cookieName = config('trackerjack.cookie.name');
        $hasCookie = $request->cookie($cookieName);

        Log::info('TrackerJack: Cookie status', [
            'cookie_name'     => $cookieName,
            'has_cookie'      => $hasCookie,
            'response_status' => $response->status(),
        ]);

        if ($response->status() === 200 && ! $hasCookie) {
            Log::info('TrackerJack: Setting cookie', ['visitor_id' => $visitorId]);

            $cookie = cookie(
                $cookieName,
                $visitorId,
                config('trackerjack.cookie.ttl'),
                '/', // path
                null, // domain
                true, // secure
                true, // httpOnly
                false, // raw
                'Lax' // sameSite
            );

            $response = $response->withCookie($cookie);
        }

        return $response;
    }

    public function __destruct()
    {
        $this->dispatchBatch();
    }

    protected function queueVisit(Request $request, string $visitorId): void
    {
        $visit = [
            'visitor_id'   => $visitorId,
            'url'          => $request->fullUrl(),
            'referrer'     => $request->header('referer'),
            'utm_source'   => $request->get('utm_source'),
            'utm_medium'   => $request->get('utm_medium'),
            'utm_campaign' => $request->get('utm_campaign'),
            'utm_term'     => $request->get('utm_term'),
            'utm_content'  => $request->get('utm_content'),
            'gclid'        => $request->get('gclid'),
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->userAgent(),
        ];

        self::$visitBuffer[] = $visit;

        if (count(self::$visitBuffer) >= self::BATCH_SIZE) {
            $this->dispatchBatch();
        }
    }

    protected function dispatchBatch(): void
    {
        if (empty(self::$visitBuffer)) {
            return;
        }

        ProcessVisitBatch::dispatch(collect(self::$visitBuffer));
        self::$visitBuffer = [];
    }

    protected function shouldTrack(Request $request): bool
    {
        if ($request->isMethod('GET') === false) {
            return false;
        }

        foreach (config('trackerjack.excluded_routes', []) as $pattern) {
            if ($request->is($pattern)) {
                return false;
            }
        }

        return true;
    }
}
