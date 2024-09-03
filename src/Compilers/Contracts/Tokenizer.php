<?php

namespace StackWeb\Compilers\Contracts;

interface Tokenizer
{

    public function parse() : void;

    /**
     * @return Token[]
     */
    public function getTokens() : array;

}