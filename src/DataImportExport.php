<?php

namespace IvanoMatteo\LaravelDataImportExport;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Iterator;
use IvanoMatteo\CsvReadWrite\CsvReader;
use IvanoMatteo\CsvReadWrite\CsvWriter;
use IvanoMatteo\LaravelDataMigrations\Pkg;

class DataImportExport
{
    private string $csvSeparator;
    private string $csvQuote;
    private string $csvEscape;

    public function __construct()
    {
        $this->csvSeparator = Pkg::configGet('csv_separator');
        $this->csvQuote = Pkg::configGet('csv_quote');
        $this->csvEscape = Pkg::configGet('csv_escape');
    }

    public function setCsvFormat(string $sep, string $quot = '"', string $esc = "\\"): static
    {
        $this->csvSeparator = $sep;
        $this->csvQuote = $quot;
        $this->csvEscape = $esc;

        return $this;
    }

    public function truncate(string|array|Collection $tables,string $connection = null): void
    {
        $connection = DB::connection($connection);
        collect($tables)->each(
            fn ($t) => $connection->table($t)->truncate()
        );
    }


    public function importTable(string $file, string|Closure $tableOrClosure = null, string $connection = null)
    {
        ['filename' => $filename] = pathinfo($file);

        if (!$tableOrClosure) {
            $tableOrClosure = Str::snake($filename);
        }

        $importer = (new BufferedImporter($connection));

        if ($tableOrClosure instanceof Closure) {
            $importer->importUsing($tableOrClosure);
        } else {
            $importer->importUsingInsert($tableOrClosure);
        }

        $importer->run($this->makeIterator($file));
    }

    public function exportTable(
        string $file,
        string|Model|EloquentBuilder|QueryBuilder $table = null,
    ) {
        ['filename' => $filename] = pathinfo($file);

        if (! $table) {
            $table = Str::snake($filename);
        }

        if ($table instanceof Model) {
            $query = $table->query()->toBase();
        } elseif ($table instanceof EloquentBuilder || $table instanceof QueryBuilder) {
            $query = $table;
        } else {
            $query = DB::table($table);
        }

        $headers = array_keys((array) ($query->first() ?? []));

        (new CsvWriter($file))
            ->format(
                $this->csvSeparator,
                $this->csvQuote,
                $this->csvEscape,
            )->write($query->cursor(), $headers);
    }

    private function makeIterator($file): Iterator
    {
        ['extension' => $ext] = pathinfo($file);

        $ext = Str::lower($ext);

        switch ($ext) {

            case 'csv':
                return (new CsvReader($file))
                    ->format(
                        $this->csvSeparator,
                        $this->csvQuote,
                        $this->csvEscape,
                    )
                    ->iterator();
        }

        return null;
    }
}
