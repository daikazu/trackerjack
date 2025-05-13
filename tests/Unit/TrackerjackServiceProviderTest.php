<?php

use Daikazu\Trackerjack\TrackerjackServiceProvider;
use Illuminate\Support\Facades\Config;

test('service provider is registered', function (): void {
    $providers = Config::get('app.providers');

    expect($providers)->toContain(TrackerjackServiceProvider::class);
});

test('package config is published', function (): void {
    $config = Config::get('trackerjack');

    expect($config)->toBeArray();
});
