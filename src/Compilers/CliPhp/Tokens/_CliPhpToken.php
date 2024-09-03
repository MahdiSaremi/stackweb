<?php

namespace StackWeb\Compilers\CliPhp\Tokens;

use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\HtmlX\Tokens\_PropValue;

readonly class _CliPhpToken implements Token, _PropValue
{

    public function __construct(
        public string $code,
    )
    {
    }

}