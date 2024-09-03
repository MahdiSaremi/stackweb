<?php

namespace StackWeb\Compilers\Stack\Tokens;

use StackWeb\Compilers\Concerns\TokenTrait;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\StringReader;

readonly class _ComponentToken implements Token
{
    use TokenTrait;

    public function __construct(
        public StringReader $reader,
        public int $startOffset,
        public int $endOffset,

        public ?string $name,
        public array $props,
        public array $tokens,
    )
    {
    }

}