<?php

namespace StackWeb\Compilers\Stack;

use StackWeb\Compilers\CliPhp\CliPhpParser;
use StackWeb\Compilers\CliPhp\Tokens\_CliPhpToken;
use StackWeb\Compilers\Contracts\Parser;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\HtmlX\Tokens\_DomText;
use StackWeb\Compilers\HtmlX\Tokens\_DomToken;
use StackWeb\Compilers\HtmlX\Tokens\_PhpScriptToken;
use StackWeb\Compilers\Stack\Structs\_StackStruct;
use StackWeb\Compilers\StringReader;

class StackParser implements Parser
{
    public _StackStruct $stack;

    public function __construct(
        protected StringReader $string,
        /** @var Token[] */
        protected array        $tokens,
    )
    {
    }

    public static function from(StringReader $string)
    {
        $tokenizer = new Tokenizer($string);
        $tokenizer->parse();

        return new static($string, $tokenizer->getTokens());
    }

    public function parse(): void
    {
        $i = 0;

        if (! (@$this->tokens[$i] instanceof _PhpScriptToken)) {
            (head($this->tokens) ?: $this->string)->syntaxError("Stackweb requires a php script at the beginning of file");
        }

        $phpInitializer = $this->tokens[$i++]->code;

        $this->skipWhitespaces($i);

        $this->stack = new Structs\_StackStruct(
            $this->string,
            $this->string->startIndex,
            $this->string->startIndex + $this->string->length,
            phpInitializer: $phpInitializer,
            dom: array_slice($this->tokens, $i),
        );
    }

    protected function skipWhitespaces(int &$i)
    {
        while (
            ($token = @$this->tokens[$i]) &&
            $token instanceof _DomText &&
            is_string($token->value) &&
            trim($token->value) === ''
        ) {
            $i++;
        }
    }

    public function getStruct(): _StackStruct
    {
        return $this->stack;
    }
}