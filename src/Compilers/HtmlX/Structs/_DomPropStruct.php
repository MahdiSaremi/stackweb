<?php

namespace StackWeb\Compilers\HtmlX\Structs;

use StackWeb\Compilers\Concerns\TokenTrait;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\Contracts\Value;
use StackWeb\Compilers\StringReader;

class _DomPropStruct implements Token, _Item
{
    use TokenTrait;

    public function __construct(
        public StringReader $reader,
        public int $startOffset,
        public int $endOffset,

        public string|Value $name,
        public string|true|Value $value,
    )
    {
    }

    public function isStatic() : bool
    {
        return !is_object($this->name) && !is_object($this->value);
    }
}