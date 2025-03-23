<?php

namespace StackWeb\Compilers\HtmlX\Tokens;

use StackWeb\Compilers\Concerns\TokenTrait;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\StringReader;

class _PreToken implements Token
{
    use TokenTrait;

    public const TEXT = 0;
    public const OPEN = 1;
    public const CLOSE = 2;
    public const SCRIPT = 3;
    public const PHP = 4;
    public const PHP_EQ = 4;

    public function __construct(
        public StringReader $reader,
        public int          $startOffset,
        public int          $endOffset,

        public int          $type,
        public mixed        $content = null,
        public ?bool        $selfClose = null,
        public ?array       $props = null,
    )
    {
    }

}