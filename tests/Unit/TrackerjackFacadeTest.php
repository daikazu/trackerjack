<?php

use Daikazu\Trackerjack\Facades\Trackerjack;

test('facade resolves to correct implementation', function (): void {
    $implementation = Trackerjack::getFacadeRoot();

    expect($implementation)->toBeInstanceOf(\Daikazu\Trackerjack\Trackerjack::class);
});
