<?php

namespace Ht3aa\QiCard;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Ht3aa\QiCard\Commands\QiCardCommand;

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
            ->hasMigration('create_qi_card_table')
            ->hasCommand(QiCardCommand::class);
    }
}
