<?php

namespace IvanoMatteo\LaravelDataImportExport\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use IvanoMatteo\LaravelDataImportExport\LaravelDataImportExportServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'IvanoMatteo\\LaravelDataImportExport\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelDataImportExportServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-data-import-export_table.php.stub';
        $migration->up();
        */
    }
}
