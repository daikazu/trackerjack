<?php

namespace Daikazu\Trackerjack\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class VisitorIdentifier
{
    public function getVisitorId(Request $request): string
    {
        $cookieName = config('trackerjack.cookie.name');

        if ($request->hasCookie($cookieName)) {
            return $request->cookie($cookieName);
        }

        Cookie::queue(
            $cookieName,
            $footprint = $this->fingerprint($request),
            config('trackerjack.cookie.ttl'),
            null,
            config('trackerjack.cookie.domain'),
        );

        return $footprint;
    }

    public function fingerprint(Request $request): string
    {
        return sha1(implode('|', array_filter([
            $request->ip(),
            $request->header('User-Agent'),
            config('trackerjack.uniqueness') ? Str::random(20) : null,
        ])));
    }
}
