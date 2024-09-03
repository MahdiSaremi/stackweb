<?php

namespace StackWeb\Compilers\HtmlX\Structs;

use StackWeb\Compilers\Concerns\TokenTrait;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\Contracts\Value;
use StackWeb\Compilers\StringReader;

class _TextStruct implements Token, _Node
{
    use TokenTrait;

    public function __construct(
        public StringReader $reader,
        public int $startOffset,
        public int $endOffset,

        public string|Value $text,

        public ?_Node $parent,
    )
    {
    }

    public function getChildren() : array
    {
        return [];
    }

    public function getParent() : ?_Node
    {
        return $this->parent;
    }

    public function isStatic() : bool
    {
        return !is_object($this->text);
    }
}