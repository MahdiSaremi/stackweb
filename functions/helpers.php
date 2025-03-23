<?php

namespace StackWeb;

use Closure;
use StackWeb\Entities\Action;
use StackWeb\Entities\Component;
use StackWeb\Entities\State;

function useComponent(): Component
{
    return Component::$instance;
}

function useState(): State
{
    return Component::$instance->useState();
}

function useAction(Closure $callback): Action
{
    return Component::$instance->useAction($callback);
}
