<?php

namespace StackWeb\Compilers\Stack\Tokens;

use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\HtmlX\Tokens\_PropValue;

readonly class _ComponentStateToken implements Token
{

    public function __construct(
        public string $name,
        public null|_PropValue $default,
    )
    {
    }

}