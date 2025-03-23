<?php

namespace StackWeb\Compilers\Stack\Structs;

use StackWeb\Compilers\Concerns\TokenTrait;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\StringReader;

class _StackStruct implements Token
{
    use TokenTrait;

    public function __construct(
        public StringReader $reader,
        public int          $startOffset,
        public int          $endOffset,

        public string       $name,
        public string       $phpInitializer,
        /** @var Token[] */
        public array        $dom,
    )
    {
    }
}