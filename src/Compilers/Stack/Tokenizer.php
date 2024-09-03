<?php

namespace StackWeb\Compilers\Stack;

use StackWeb\Compilers\Contracts\Tokenizer as TokenizerContract;

class Tokenizer implements TokenizerContract
{

    protected array $tokens;

    public function parse() : void
    {
        $this->tokens = [];

        return $this;
    }

    public function getTokens() : array
    {
        return $this->tokens;
    }

}