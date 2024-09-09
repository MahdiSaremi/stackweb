<?php

namespace StackWeb\Compilers\CliPhp;

use PhpParser\Node\Expr;
use StackWeb\Compilers\CliPhp\Structs\_CliPhpStruct;
use StackWeb\Compilers\CliPhp\Tokens\_CliPhpToken;
use StackWeb\Compilers\Contracts\Parser;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Renderer\Builder\StringBuilder;

class CliPhpParser implements Parser
{

    public function __construct(
        public _CliPhpToken $token,
    )
    {
    }

    public StringBuilder $js;

    public function parse() : void
    {
        $this->js = new StringBuilder();

        $this->parseExpr($this->token->expr);
    }

    public function parseExpr(Expr $expr)
    {
        
    }

    public function getStruct() : Token
    {
        return new _CliPhpStruct(
            $this->token->reader, $this->token->startOffset, $this->token->endOffset,
            $this->token->phpCode,
            $this->token->phpCode,
        );
    }

}