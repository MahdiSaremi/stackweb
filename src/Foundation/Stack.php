<?php

namespace StackWeb\Foundation;

class Stack
{

    public function __construct(
        protected array $components,
    )
    {
    }

    public function has(string $name)
    {
        return array_key_exists($name, $this->components);
    }

    public function get(string $name) : ?Component
    {
        if (!$this->has($name)) return null;

        if ($this->components[$name] instanceof \Closure)
        {
            $this->components[$name] = $this->components[$name]();
        }

        return $this->components[$name];
    }

    public function create(string $name) : ?ComponentContainer
    {
        return $this->get($name)?->create();
    }

}