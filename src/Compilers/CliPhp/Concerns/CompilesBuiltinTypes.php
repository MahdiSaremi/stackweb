<?php

namespace StackWeb\Compilers\CliPhp\Concerns;
use PhpParser\Node\Scalar;
use StackWeb\Renderer\JsRenderer;

trait CompilesBuiltinTypes
{

    public function scalarString(Scalar\String_ $string)
    {
        $this->js->append(JsRenderer::render($string->value));
    }

}