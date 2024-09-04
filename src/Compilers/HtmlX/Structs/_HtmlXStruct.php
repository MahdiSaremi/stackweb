<?php

namespace StackWeb\Compilers\HtmlX\Structs;

use StackWeb\Compilers\Concerns\TokenTrait;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\StringReader;

class _HtmlXStruct implements Token
{
    use TokenTrait;

    public function __construct(
        public StringReader $reader,
        public int $startOffset,
        public int $endOffset,

        /** @var _Node[] */
        public array $nodes,
    )
    {
    }

}