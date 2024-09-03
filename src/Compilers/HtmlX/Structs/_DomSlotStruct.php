<?php

namespace StackWeb\Compilers\HtmlX\Structs;

use StackWeb\Compilers\Concerns\TokenTrait;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\StringReader;

class _DomSlotStruct implements Token, _Node
{
    use TokenTrait;

    public function __construct(
        public StringReader $reader,
        public int $startOffset,
        public int $endOffset,

        public string $name,
        /** @var _DomPropStruct[] */
        public array $props,
        /** @var _Node[] */
        public array $inner,

        public ?_Node $parent,
    )
    {
    }

    protected bool $isStatic;

    public function isStatic() : bool
    {
        if (!isset($this->isStatic))
        {
            $this->isStatic = true;
            foreach ($this->props as $prop)
            {
                if (!$prop->isStatic())
                {
                    return $this->isStatic = false;
                }
            }
            foreach ($this->inner as $child)
            {
                if (!$child->isStatic())
                {
                    return $this->isStatic = false;
                }
            }
        }

        return $this->isStatic;
    }

    public function getChildren() : array
    {
        return $this->inner;
    }

    public function getParent() : ?_Node
    {
        return $this->parent;
    }
}