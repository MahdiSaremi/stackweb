<?php

namespace StackWeb\Entities;

class Action extends Entity
{
    public function __construct(
        Component $component,
        protected \Closure $callback,
    )
    {
        parent::__construct($component);
    }
}