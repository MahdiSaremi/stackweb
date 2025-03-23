<?php

namespace StackWeb\Entities;

class Component extends Entity
{
    public static Component $instance;

    protected array $states = [];
    protected array $actions = [];

    public function useState(): State
    {
        $state = new State($this);
        $this->states[] = $state;

        return $state;
    }

    public function useAction(\Closure $callback): Action
    {
        $action = new Action($this, $callback);
        $this->actions[] = $action;

        return $action;
    }
}