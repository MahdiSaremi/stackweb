<?php

namespace StackWeb\Compilers\Stack;

use StackWeb\Compilers\Contracts\Tokenizer as TokenizerContract;
use StackWeb\Compilers\StringReader;

class Tokenizer implements TokenizerContract
{
    public function __construct(
        protected StringReader $string,
    )
    {
    }

    protected array $tokens;

    public function parse(): void
    {
        $tokenizer = new \StackWeb\Compilers\HtmlX\Tokenizer($this->string);
        $tokenizer->parse();

        $this->tokens = $tokenizer->tokens;
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }
}