<?php

namespace StackWeb\Foundation;

class Component
{

    public function __construct(
        public readonly string $name,
    )
    {
    }

    public static function make(string $name)
    {
        return new static($name);
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

    public $renderApi;
    public $renderCli;

    public function renderApi($callback)
    {
        $this->renderApi = $callback;
        return $this;
    }

    public function renderCli($callback)
    {
        $this->renderCli = $callback;
        return $this;
    }

    public array $apiResults;

    public function apiResults(array $items)
    {
        $this->apiResults = $items;
        return $this;
    }

    public array $depComponents = [];

    public function depComponents(array $deps)
    {
        $this->depComponents = $deps;
        return $this;
    }



    public function create()
    {
        return new ComponentContainer($this);
    }

}