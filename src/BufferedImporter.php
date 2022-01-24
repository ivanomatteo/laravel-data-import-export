<?php

namespace IvanoMatteo\LaravelDataImportExport;

use Generator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Iterator;

/**
 * @property string $table
 * @property \Illuminate\Database\ConnectionInterface $connection
 * @property array $buff
 * @property int $buffSize
 *
 * @property bool $truncate
 *
 * @property callable|null $importLogic
 *
 * @property int $counter
 *
 */
class BufferedImporter
{
    private $table;
    private $connection;

    private $buff;
    private $buffSize = 100;
    private $truncate = false;

    private $importLogic = null;

    private $counter = 0;

    public function __construct($table = null, $connection = null)
    {
        $this->table = $table;
        $this->connection = DB::connection($connection);
    }


    public function bufferSize($buffSize)
    {
        $this->buffSize = $buffSize;
        return $this;
    }


    public function truncate($b = true)
    {
        $this->truncate = $b;
        return $this;
    }

    public function importLogic(callable $importLogic)
    {
        $this->importLogic = $importLogic;
        return $this;
    }

    public function run(Iterator $iterator)
    {
        $this->connection->transaction(function () use ($iterator) {
            if ($this->truncate) {
                $this->connection->table($this->table)->truncate();
            }

            $this->buff = [];
            $this->counter = 0;
            foreach ($iterator as $row) {
                $this->processRow($row);
                $this->counter++;
            }
            $this->flushBuffer();
        });
    }

    private function processRow($row)
    {
        $this->buff[] = $row;

        if ($this->counter && $this->counter % $this->buffSize === 0) {
            $this->flushBuffer();
        }
    }

    private function flushBuffer()
    {
        if (!empty($this->buff)) {
            if ($this->importLogic) {
                ($this->importLogic)($this->table, $this->buff);
            } else {
                $this->connection->table($this->table)->insert($this->buff);
            }

            $this->buff = [];
        }
    }
}
