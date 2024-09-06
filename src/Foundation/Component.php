<?php

namespace StackWeb\Foundation;

class Component
{

    public static function make()
    {
        return new static;
    }

    public array $props = [];

    public function prop(string $name, $default, $defaultJs)
    {
        $this->props[$name] = [$default, $defaultJs];
        return $this;
    }

    public array $states = [];

    public function state(string $name, $default, $defaultJs)
    {
        $this->states[$name] = [$default, $defaultJs];
        return $this;
    }

    public array $slots = [];

    public function slot(string $name, $default, $defaultJs)
    {
        $this->slots[$name] = [$default, $defaultJs];
        return $this;
    }

    public $render;
    public $renderJs;

    public function render($callback, $js)
    {
        $this->render = $callback;
        $this->renderJs = $js;
        return $this;
    }


    public function create()
    {
        return new ComponentContainer($this);
    }

}