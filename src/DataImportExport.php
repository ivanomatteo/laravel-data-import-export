<?php

namespace IvanoMatteo\LaravelDataImportExport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
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


    public function importTable(string $file, string $table = null, bool $truncate = false, string $connection = null)
    {
        ['filename' => $filename] = pathinfo($file);

        if (!$table) {
            $table = Str::snake($filename);
        }

        (new BufferedImporter($connection))
            ->importUsingInsert($table)
            ->truncate($truncate ? $table : [])
            ->run($this->makeIterator($file));
    }

    public function exportTable(
        string $file,
        string|Model|EloquentBuilder|QueryBuilder $table = null,
    ) {
        ['filename' => $filename] = pathinfo($file);

        if (!$table) {
            $table = Str::snake($filename);
        }

        if ($table instanceof Model) {
            $query = $table->query()->toBase();
        } else if ($table instanceof EloquentBuilder || $table instanceof QueryBuilder) {
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
