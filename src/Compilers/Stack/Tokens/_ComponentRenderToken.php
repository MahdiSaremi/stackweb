<?php

namespace StackWeb\Compilers\Stack\Tokens;

use StackWeb\Compilers\Contracts\Token;

readonly class _ComponentRenderToken implements Token
{

    public function __construct(
        public array $content,
    )
    {
    }

}