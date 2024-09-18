<?php

namespace StackWeb\Compilers\CliPhp;

use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use StackWeb\Compilers\CliPhp\Structs\_CliPhpStruct;
use StackWeb\Compilers\CliPhp\Tokens\_CliPhpToken;
use StackWeb\Compilers\Contracts\Parser;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Renderer\Builder\StringBuilder;

class CliPhpParser implements Parser
{
    use Concerns\CompilesAccessing,
        Concerns\CompilesOperators,
        Concerns\CompilesBuiltinTypes;

    public function __construct(
        public _CliPhpToken $token,
    )
    {
    }

    public StringBuilder $js;

    public function parse() : void
    {
        $this->js = new StringBuilder();

        $this->expr($this->token->expr);
        // dd($this->js->toCode());
    }

    public function expr(Expr $expr)
    {
        match (get_class($expr))
        {
            Expr\BinaryOp\Plus::class => $this->binaryOpPlus($expr),

            Scalar\String_::class => $this->scalarString($expr),

            default => dd($expr),
        };
    }

    public function getStruct() : Token
    {
        return new _CliPhpStruct(
            $this->token->reader, $this->token->startOffset, $this->token->endOffset,
            $this->token->phpCode,
            $this->js->toCode(),
        );
    }

}