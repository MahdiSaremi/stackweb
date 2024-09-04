<?php

namespace StackWeb\Compilers\CliPhp\Structs;

use StackWeb\Compilers\Concerns\TokenTrait;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\Contracts\Value;
use StackWeb\Compilers\StringReader;

class _CliPhpStruct implements Token, Value
{
    use TokenTrait;

    public function __construct(
        public StringReader $reader,
        public int $startOffset,
        public int $endOffset,

        public string $php, // todo
        public string $js, // todo
    )
    {
    }

}