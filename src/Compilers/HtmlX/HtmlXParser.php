<?php

namespace StackWeb\Compilers\HtmlX;

use StackWeb\Compilers\ApiPhp\ApiPhpParser;
use StackWeb\Compilers\ApiPhp\Tokens\_ApiPhpToken;
use StackWeb\Compilers\CliPhp\CliPhpParser;
use StackWeb\Compilers\CliPhp\Tokens\_CliPhpToken;
use StackWeb\Compilers\Contracts\Parser;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\HtmlX\Structs\_HtmlXStruct;
use StackWeb\Compilers\HtmlX\Structs\_Node;
use StackWeb\Compilers\StringReader;

class HtmlXParser implements Parser
{

    public array $nodes;

    public function __construct(
        public readonly Token $base,
        public readonly array $tokens,
    )
    {
    }

    public function parse() : void
    {
        $this->nodes = $this->convertNodes($this->tokens);
    }

    public function convertNodes(array $tokens, ?_Node $parent = null) : array
    {
        $nodes = [];
        foreach ($tokens as $token)
        {
            if ($token instanceof Tokens\_DomToken)
            {
                $props = [];
                foreach ($token->props as $prop)
                {
                    $props[] = new Structs\_DomPropStruct(
                        $prop->reader,
                        $prop->startOffset,
                        $prop->endOffset,
                        $this->convertValue($prop->name),
                        $this->convertValue($prop->value),
                    );
                }

                $node = new Structs\_DomStruct(
                    $token->reader, $token->startOffset, $token->endOffset,
                    $this->convertValue($token->name),
                    $props,
                    null,
                    $parent,
                );

                $node->slot = isset($token->inner) ? $this->convertNodes($token->inner, $node) : null;

                $nodes[] = $node;
            }
            elseif ($token instanceof Tokens\_DomText)
            {
                $nodes[] = new Structs\_TextStruct(
                    $token->reader, $token->startOffset, $token->endOffset,
                    $this->convertValue($token->value),
                    $parent,
                );
            }
            else
            {
                $token->syntaxError("Unknown token");
            }
        }

        return $nodes;
    }

    public function convertValue(mixed $value)
    {
        if ($value instanceof _ApiPhpToken)
        {
            $parser = new ApiPhpParser($value);
            $parser->parse();
            return $parser->getStruct();
        }
        elseif ($value instanceof _CliPhpToken)
        {
            $parser = new CliPhpParser($value);
            $parser->parse();
            return $parser->getStruct();
        }

        return $value;
    }

    public function getStruct() : Token
    {
        return new _HtmlXStruct(
            $this->base->getReader(), $this->base->getStartOffset(), $this->base->getEndOffset(),
            $this->nodes,
        );
    }

}