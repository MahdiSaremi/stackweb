<?php

namespace StackWeb\Compilers\ApiPhp\Structs;

use StackWeb\Compilers\Concerns\TokenTrait;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\Contracts\Value;
use StackWeb\Compilers\StringReader;

class _ApiPhpStruct implements Token, Value
{
    use TokenTrait;

    public function __construct(
        public StringReader $reader,
        public int $startOffset,
        public int $endOffset,

        public string $php,
    )
    {
    }

}