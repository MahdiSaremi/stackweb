<?php

namespace StackWeb\Compilers\HtmlX\Tokens;

use StackWeb\Compilers\Contracts\Token;

readonly class _PropToken implements Token
{

    public function __construct(
        public string|_PropValue $name,
        public string|true|_PropValue $value,
    )
    {
    }

}