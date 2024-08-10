<?php

namespace StackWeb;

use Illuminate\Support\Facades\Facade;

class StackWeb extends Facade
{

    protected static function getFacadeAccessor()
    {
        return StackWebFactory::class;
    }

}