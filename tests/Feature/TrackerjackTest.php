<?php

beforeEach(function (): void {

    // Set up database configuration
    $this->app['config']->set('database.default', 'testing');
    $this->app['config']->set('database.connections.testing', [
        'driver'   => 'sqlite',
        'database' => ':memory:',
        'prefix'   => '',
    ]);

    $this->loadMigrationsFrom(__DIR__ . '/../fixtures/migrations');

    // Create the cart tables
    $this->artisan('migrate', ['--database' => 'testing']);

    $this->app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

})->todo();
