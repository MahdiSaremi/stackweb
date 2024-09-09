<?php

namespace StackWeb\Compilers\Stack\Structs;

use StackWeb\Compilers\Concerns\TokenTrait;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\HtmlX\Structs\_HtmlXStruct;
use StackWeb\Compilers\StringReader;

class _ComponentStruct implements Token
{
    use TokenTrait;

    public function __construct(
        public StringReader $reader,
        public int $startOffset,
        public int $endOffset,

        public string $name,
    )
    {
    }

    /** @var _ComponentPropStruct[] */
    public array $props;

    /** @var _ComponentSlotStruct[] */
    public array $slots;

    /** @var _ComponentStateStruct[] */
    public array $states;

    public _HtmlXStruct $render;

    public array $depComponents = [];

    public function depComponent(string $component)
    {
        if (!in_array($component, $this->depComponents))
        {
            $this->depComponents[] = $component;
        }
    }

}