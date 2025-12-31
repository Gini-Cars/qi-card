<?php

namespace Ht3aa\QiCard;

use Ht3aa\QiCard\Commands\QiCardCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class QiCardServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('qi-card')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations('create_qi_card_users_table')
            ->hasCommand(QiCardCommand::class);
    }
}
