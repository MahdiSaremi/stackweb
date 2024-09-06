<?php

namespace StackWeb\Renderer\Scope;

use StackWeb\Compilers\ApiPhp\Structs\_ApiPhpStruct;
use StackWeb\Compilers\Stack\Structs\_ComponentStruct;
use StackWeb\Renderer\Contracts\SourceRenderer;

class ComponentScope
{

    protected \WeakMap $apiResults;

    public function __construct(
        public readonly SourceRenderer $renderer,
        public readonly _ComponentStruct $component,
    )
    {
        $this->apiResults = new \WeakMap;
    }

    public function apiResult(_ApiPhpStruct $value) : string
    {
        if (isset($this->apiResults[$value]))
        {
            return $this->apiResults[$value];
        }
        else
        {
            return $this->apiResults[$value] = 'r' . $this->apiResults->count();
        }
    }

    public function getApiResults() : \WeakMap
    {
        return $this->apiResults;
    }

}