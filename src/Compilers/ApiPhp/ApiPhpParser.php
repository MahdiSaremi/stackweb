<?php

namespace StackWeb\Compilers\ApiPhp;

use StackWeb\Compilers\ApiPhp\Structs\_ApiPhpStruct;
use StackWeb\Compilers\ApiPhp\Tokens\_ApiPhpToken;
use StackWeb\Compilers\Contracts\Parser;
use StackWeb\Compilers\Contracts\Token;

class ApiPhpParser implements Parser
{

    public function __construct(
        public _ApiPhpToken $token,
    )
    {
    }

    public function parse() : void
    {
    }

    public function getStruct() : Token
    {
        return new _ApiPhpStruct(
            $this->token->reader, $this->token->startOffset, $this->token->endOffset,
            $this->token->code,
        );
    }

}