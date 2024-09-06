<?php

namespace StackWeb\Renderer\Builder;

use StackWeb\Renderer\PhpRenderer;

class SourceBuilder
{

    protected string $source = '';

    public function append(string $code)
    {
        $this->source .= $code;
        return $this;
    }

    public function appendObject($object)
    {
        return $this->append(PhpRenderer::render($object));
    }

    public function appendString(\Closure $callback)
    {
        $str = new StringBuilder();
        $callback($str);
        return $this->append($str->toCode());
    }

    public function prepend(string $code)
    {
        $this->source = $code . $this->source;
        return $this;
    }

    public function prependObject($object)
    {
        return $this->prepend(PhpRenderer::render($object));
    }

    public function prependString(\Closure $callback)
    {
        $str = new StringBuilder();
        $callback($str);
        return $this->prepend($str->toCode());
    }


    public function toCode()
    {
        return $this->source;
    }

}