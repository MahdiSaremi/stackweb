<?php

namespace StackWeb\Compilers\CliPhp;

use StackWeb\Compilers\CliPhp\Structs\_CliPhpStruct;
use StackWeb\Compilers\CliPhp\Tokens\_CliPhpToken;
use StackWeb\Compilers\Contracts\Parser;
use StackWeb\Compilers\Contracts\Token;

class CliPhpParser implements Parser
{

    public function __construct(
        public _CliPhpToken $token,
    )
    {
    }

    public function parse() : void
    {
    }

    public function getStruct() : Token
    {
        return new _CliPhpStruct(
            $this->token->reader, $this->token->startOffset, $this->token->endOffset,
            $this->token->code,
            $this->token->code,
        );
    }

}