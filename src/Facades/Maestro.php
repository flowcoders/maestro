<?php

namespace Flowcoders\Maestro\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Flowcoders\Maestro\Maestro
 */
class Maestro extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Flowcoders\Maestro\Maestro::class;
    }
}
