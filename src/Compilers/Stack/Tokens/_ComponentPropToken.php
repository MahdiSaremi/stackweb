<?php

namespace StackWeb\Compilers\Stack\Tokens;

use StackWeb\Compilers\Contracts\Token;

readonly class _ComponentPropToken implements Token
{

    public function __construct(
        public string $name,
        public mixed $default,
    )
    {
    }

}