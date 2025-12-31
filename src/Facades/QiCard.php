<?php

namespace Ht3aa\QiCard\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ht3aa\QiCard\QiCard
 */
class QiCard extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Ht3aa\QiCard\QiCard::class;
    }
}
