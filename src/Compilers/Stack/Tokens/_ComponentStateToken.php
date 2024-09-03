<?php

namespace StackWeb\Compilers\Stack\Tokens;

use StackWeb\Compilers\Concerns\TokenTrait;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\HtmlX\Tokens\_PropValue;
use StackWeb\Compilers\StringReader;

readonly class _ComponentStateToken implements Token
{
    use TokenTrait;

    public function __construct(
        public StringReader $reader,
        public int $startOffset,
        public int $endOffset,

        public string $name,
        public null|_PropValue $default,
    )
    {
    }

}