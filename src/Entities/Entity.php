<?php

namespace StackWeb\Entities;

abstract class Entity
{
    public function __construct(
        public readonly Component $component,
    )
    {
    }
}