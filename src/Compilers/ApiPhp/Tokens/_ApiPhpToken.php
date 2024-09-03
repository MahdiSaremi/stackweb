<?php

namespace StackWeb\Compilers\ApiPhp\Tokens;

use StackWeb\Compilers\Concerns\TokenTrait;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\HtmlX\Tokens\_PropValue;
use StackWeb\Compilers\StringReader;

readonly class _ApiPhpToken implements Token, _PropValue
{
    use TokenTrait;

    public function __construct(
        public StringReader $reader,
        public int $startOffset,
        public int $endOffset,

        public string $code,
    )
    {
    }

}