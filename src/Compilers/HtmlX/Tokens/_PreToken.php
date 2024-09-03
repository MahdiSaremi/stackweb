<?php

namespace StackWeb\Compilers\HtmlX\Tokens;

use StackWeb\Compilers\Concerns\TokenTrait;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\StringReader;

class _PreToken implements Token
{
    use TokenTrait;

    public function __construct(
        public StringReader $reader,
        public int $startOffset,
        public int $endOffset,

        public string $type,
        public mixed $content = null,
        public ?bool $selfClose = null,
        public ?array $props = null,
    )
    {
    }

}