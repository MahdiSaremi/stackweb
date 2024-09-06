<?php

namespace StackWeb\Builder;

class Component
{

    public static function make()
    {
        return new static;
    }

    protected array $props = [];

    public function prop(string $name, $default, $defaultJs)
    {
        $this->props[$name] = [$default, $defaultJs];
        return $this;
    }

    protected array $states = [];

    public function state(string $name, $default, $defaultJs)
    {
        $this->states[$name] = [$default, $defaultJs];
        return $this;
    }

    protected array $slots = [];

    public function slot(string $name, $default, $defaultJs)
    {
        $this->slots[$name] = [$default, $defaultJs];
        return $this;
    }

    protected $render;
    protected $renderJs;

    public function render($callback, $js)
    {
        $this->render = $callback;
        $this->renderJs = $js;
        return $this;
    }

}