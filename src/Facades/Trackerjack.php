<?php

namespace Daikazu\Trackerjack\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Daikazu\Trackerjack\Trackerjack
 */
class Trackerjack extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'trackerjack';
    }
}
