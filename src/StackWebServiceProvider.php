<?php

namespace StackWeb;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use StackWeb\Api\StackApi;
use StackWeb\Compiler\StackWebCompiler;

class StackWebServiceProvider extends ServiceProvider
{

    public function register()
    {
        View::addExtension('stack.php', 'stack-web', function () {
            return new Engine\StackWebEngine();
        });
    }

}