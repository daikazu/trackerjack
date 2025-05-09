<?php

declare(strict_types=1);

namespace Daikazu\Trackerjack;

use Daikazu\Trackerjack\Http\Middleware\TrackVisits;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TrackerjackServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('trackerjack')
            ->hasConfigFile()
            ->hasMigration('create_trackerjack_tables')
            ->hasCommand(Commands\PruneVisitsCommand::class)
            ->hasCommand(Commands\PruneEventsCommand::class)
            ->hasCommand(Commands\TrackerjackTuiCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('trackerjack', function ($app) {
            return new Trackerjack;
        });
    }

    public function packageBooted(): void
    {
        $this->app['router']->aliasMiddleware('trackerjack', TrackVisits::class);
    }
}
