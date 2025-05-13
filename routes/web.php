<?php

use Daikazu\Trackerjack\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

Route::post('trackerjack/events', [EventController::class, 'store'])
    ->name('trackerjack.events.store')
    ->middleware(['web', 'trackerjack']);
