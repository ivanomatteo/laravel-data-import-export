<?php

namespace IvanoMatteo\LaravelDataImportExport;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use IvanoMatteo\LaravelDataImportExport\Commands\LaravelDataImportExportCommand;

class LaravelDataImportExportServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-data-import-export')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-data-import-export_table')
            ->hasCommand(LaravelDataImportExportCommand::class);
    }
}
