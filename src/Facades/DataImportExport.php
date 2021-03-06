<?php

namespace IvanoMatteo\LaravelDataImportExport\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \IvanoMatteo\LaravelDataImportExport\DataImportExport
 */
class DataImportExport extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-data-import-export';
    }
}
