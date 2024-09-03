<?php

namespace StackWeb\Compilers\HtmlX\Tokens;

use StackWeb\Compilers\Contracts\Token;

readonly class _DomText implements Token
{

    public function __construct(
        public string|_PropValue $value,
    )
    {
    }

}