<?php

namespace IvanoMatteo\LaravelDataImportExport\Commands;

use Illuminate\Console\Command;

class LaravelDataImportExportCommand extends Command
{
    public $signature = 'laravel-data-import-export';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
