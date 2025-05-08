<?php

namespace Daikazu\Trackerjack;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Daikazu\Trackerjack\Commands\TrackerjackCommand;

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
            ->hasViews()
            ->hasMigration('create_trackerjack_table')
            ->hasCommand(TrackerjackCommand::class);
    }
}
