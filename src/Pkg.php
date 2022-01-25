<?php

namespace IvanoMatteo\LaravelDataMigrations;

use Illuminate\Support\Facades\Config;

class Pkg
{

    public static function getName(): string
    {
        return 'laravel-data-import-export';
    }

    public static function configGet(string $key, mixed $default = null): mixed
    {
        return Config::get(static::getName() . ".$key", $default);
    }
    public static function configSet(string $key, mixed $value): void
    {
        Config::set(static::getName() . ".$key", $value);
    }
}
