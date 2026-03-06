<?php

namespace NextMigrant\Sendy;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SendyServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('sendy')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(SendyService::class, function () {
            return new SendyService;
        });
    }
}

