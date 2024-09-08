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


    public function create()
    {
        return new ComponentContainer($this);
    }

}