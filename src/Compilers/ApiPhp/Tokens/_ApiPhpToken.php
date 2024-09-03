<?php

namespace StackWeb\Compilers\ApiPhp\Tokens;

use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\HtmlX\Tokens\_PropValue;

readonly class _ApiPhpToken implements Token, _PropValue
{

    public function __construct(
        public string $code,
    )
    {
    }

}