<?php

namespace StackWeb;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use StackWeb\Api\StackApi;
use StackWeb\Compiler\StackWebCompiler;

class StackWebServiceProvider extends ServiceProvider
{

    public function register()
    {
        Blade::precompiler(fn () => app(StackWebCompiler::class)->render(...func_get_args()));
        Blade::component(Components\Blade\JsTextComponent::class, 'js::text');
        Blade::component(Components\Blade\JsHtmlComponent::class, 'js::html');
    }

}