<?php

use Daikazu\Trackerjack\Http\Controllers\EventController;

\Illuminate\Support\Facades\Route::post('trackerjack/events', [EventController::class, 'store'])
    ->name('trackerjack.events.store')
    ->middleware(['web', 'trackerjack']);
