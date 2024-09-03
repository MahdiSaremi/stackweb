<?php

namespace StackWeb\Compilers\HtmlX;

use StackWeb\Compilers\Contracts\Parser;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\StringReader;

class HtmlXParser implements Parser
{

    public array $structs;

    public function __construct(
        public readonly array $tokens,
    )
    {
    }

    public static function fromReader(StringReader $string)
    {
        $tokenizer = new Tokenizer($string);
        $tokenizer->parse();

        return new static($tokenizer->getTokens());
    }

    public function parse() : void
    {
        foreach ($this->tokens as $token)
        {

        }
    }

    public function convertNodes(array $tokens) : array
    {
        $new = [];
        foreach ($tokens as $token)
        {
            if ($token instanceof Tokens\_DomToken)
            {
                foreach ($token->props as $prop)
                {
                    $prop = $this->convertValue($prop);.....
                }

                $new[] = new Structs\_DomStruct(
                    $token->reader,
                    $token->startOffset,
                    $token->endOffset,
                    $this->convertValue($token->name),
                    $this->convertProps($token->props),
                );
            }
        }

        return $new;
    }

    public function convert(Token $token)
    {

    }

    public function getStructs() : array
    {
        // TODO: Implement getStructs() method.
    }

}