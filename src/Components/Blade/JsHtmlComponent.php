<?php

namespace StackWeb\Components\Blade;

use Illuminate\Support\HtmlString;
use Illuminate\View\Component;
use JsPhpize\JsPhpize;
use StackWeb\StackWeb;

class JsHtmlComponent extends Component
{

    public function __construct(
        public string $value,
        public string $tag = 'span',
        public $fallback = null,
    )
    {
    }

    public function getFallback()
    {
        if (isset($this->fallback))
        {
            return $this->fallback;
        }

        /** @var \StackWeb\Component $comp */
        $comp = StackWeb::peek();

        return new HtmlString((new JsPhpize())->renderCode("return ($this->value)", $comp->__getJsData()));
    }

    public function render()
    {
        return "<{$this->tag} x-html=\"".e($this->value)."\" {$this->attributes?->toHtml()}>" .
            e($this->getFallback()) .
            "</{$this->tag}>";
    }

}