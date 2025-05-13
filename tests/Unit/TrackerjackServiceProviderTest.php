<?php

use Daikazu\Trackerjack\TrackerjackServiceProvider;
use Illuminate\Support\Facades\Config;

test('service provider is registered', function () {
    $providers = Config::get('app.providers');
    
    expect($providers)->toContain(TrackerjackServiceProvider::class);
});

test('package config is published', function () {
    $config = Config::get('trackerjack');
    
    expect($config)->toBeArray();
}); 