<?php

namespace StackWeb\Tests;

use StackWeb\StackWebServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{

    protected function getPackageProviders($app)
    {
        return [
            ...parent::getPackageProviders($app),
            StackWebServiceProvider::class,
        ];
    }

}