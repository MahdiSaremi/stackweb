<?php

namespace StackWeb;

use Illuminate\Support\Facades\Blade;

class Component
{

    protected $__js_data;

    public function __getJsData()
    {
        return $this->__js_data ??= $this->__runJsData();
    }

    protected function __runJsData()
    {
        return [];
    }


    public function render()
    {
        StackWeb::push($this);
        return tap($this->renderContent(), function()
        {
            StackWeb::pop();
        });
    }

    protected function renderContent()
    {
        return Blade::render($this->renderBlade(), [
            'component' => $this,
            'self' => $this,
        ]);
    }

    protected function renderBlade()
    {
        return '';
    }

}