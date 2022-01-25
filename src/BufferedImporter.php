<?php

namespace IvanoMatteo\LaravelDataImportExport;

use Closure;
use Exception;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Iterator;

class BufferedImporter
{
    private ConnectionInterface $connection;

    private array $buff = [];
    private int $buffSize = 100;
    private Collection $truncate;

    private ?Closure $importLogic = null;

    private int $counter = 0;

    public function __construct(string $connectionName = null)
    {
        $this->truncate = new Collection();
        $this->connection = DB::connection($connectionName);
    }

    public function bufferSize(int $buffSize): static
    {
        $this->buffSize = $buffSize;

        return $this;
    }

    public function truncate(array|string $table): static
    {
        $this->truncate = $this->truncate->merge(collect($table));

        return $this;
    }

    public function importUsing(callable $importLogic): static
    {
        $this->importLogic = Closure::fromCallable($importLogic);

        return $this;
    }

    public function importUsingInsert(string $table): static
    {
        $this->importLogic = fn ($buff) => $this->connection->table($table)->insert($buff);

        return $this;
    }

    public function run(Iterator $iterator): void
    {
        if (empty($this->importLogic)) {
            throw new Exception('Import logic not defined');
        }

        $this->connection->transaction(function () use ($iterator) {
            $this->truncateTables();

            $this->buff = [];
            $this->counter = 0;
            foreach ($iterator as $row) {
                $this->processRow($row);
                $this->counter++;
            }
            $this->flushBuffer();
        });
    }

    private function processRow($row): void
    {
        $this->buff[] = $row;

        if ($this->counter && $this->counter % $this->buffSize === 0) {
            $this->flushBuffer();
        }
    }

    private function truncateTables(): void
    {
        if ($this->truncate) {
            $this->truncate->each(
                fn ($table) => $this->connection->table($table)->truncate()
            );
        }
    }

    private function flushBuffer(): void
    {
        if (! empty($this->buff)) {
            ($this->importLogic)($this->buff);
            $this->buff = [];
        }
    }
}
