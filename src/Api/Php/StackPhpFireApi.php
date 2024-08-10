<?php

namespace StackWeb\Api\Php;

use StackWeb\Api\StackApi;

class StackPhpFireApi
{

    public function __construct(
        protected StackApi $api,
    )
    {
    }

    public function buildJs(string $js, StackComponentClass $component)
    {
        return preg_replace_callback_array([
            '/([^\w])use\s*\{\{(.*?)\}\}/' => function($matches) use($component) {
                $uuid = md5(rand(1, 9999));
                return $matches[1] . "server.prop(\"$uuid\")";
            },
        ], $js);
    }

}