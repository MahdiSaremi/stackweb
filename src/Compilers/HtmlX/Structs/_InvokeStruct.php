<?php

namespace StackWeb\Compilers\HtmlX\Structs;

use StackWeb\Compilers\Concerns\TokenTrait;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\StringReader;

class _InvokeStruct implements Token, _Node
{
    use TokenTrait;

    public function __construct(
        public StringReader $reader,
        public int $startOffset,
        public int $endOffset,

        public string $name,
        /** @var _DomPropStruct[] */
        public array $props,
        /** @var _DomSlotStruct[] */
        public array $slots,

        public ?_Node $parent,
    )
    {
    }

    public function isStatic() : bool
    {
        return false;
    }

    public function getChildren() : array
    {
        return [];
    }

    public function getParent() : ?_Node
    {
        return $this->parent;
    }
}