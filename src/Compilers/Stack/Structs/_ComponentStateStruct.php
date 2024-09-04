<?php

namespace StackWeb\Compilers\Stack\Structs;

use StackWeb\Compilers\Concerns\TokenTrait;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\StringReader;

class _ComponentStateStruct implements Token
{
    use TokenTrait;

    public function __construct(
        public StringReader $reader,
        public int $startOffset,
        public int $endOffset,

        public string $name,
        public mixed $default, // todo
    )
    {
    }

}