<?php

namespace StackWeb\Compilers\HtmlX\Tokens;

use StackWeb\Compilers\Contracts\Token;

readonly class _DomToken implements Token
{

    public function __construct(
        public string|_PropValue $name,
        /** @var _PropToken[] */
        public array $props,
        public bool $selfClose,
        public ?array $inner,
    )
    {
    }

}