<?php

namespace StackWeb\Compilers\HtmlX;

use Illuminate\Support\Arr;
use StackWeb\Compilers\ApiPhp\ApiPhpParser;
use StackWeb\Compilers\ApiPhp\Tokens\_ApiPhpToken;
use StackWeb\Compilers\CliPhp\CliPhpParser;
use StackWeb\Compilers\CliPhp\Tokens\_CliPhpToken;
use StackWeb\Compilers\Contracts\Parser;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\HtmlX\Structs\_HtmlXStruct;
use StackWeb\Compilers\HtmlX\Structs\_Node;
use StackWeb\Compilers\Stack\Structs\_ComponentStruct;
use StackWeb\Compilers\Stack\Structs\_StackStruct;
use StackWeb\Compilers\StringReader;

class HtmlXParser implements Parser
{

    public array $nodes;

    public function __construct(
        public readonly _StackStruct $stack,
        public readonly ?_ComponentStruct $component,
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
                if ($invoke = $this->parseInvoke($token, $parent))
                {
                    $nodes[] = $invoke;
                }
                else
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

    public function parseInvoke(Tokens\_DomToken $dom, ?_Node $parent = null)
    {
        if (is_string($dom->name) && strlen($dom->name) && $dom->name[0] !== lcfirst($dom->name[0]))
        {
            $component = $this->stack->resolveAliasComponent($dom->name) ?? $dom->name;

            $this->component?->depComponent($component);

            $props = [];
            foreach ($dom->props as $prop)
            {
                $props[] = new Structs\_DomPropStruct(
                    $prop->reader,
                    $prop->startOffset,
                    $prop->endOffset,
                    $this->convertValue($prop->name),
                    $this->convertValue($prop->value),
                );
            }

            $node = new Structs\_InvokeStruct(
                $dom->reader,
                $dom->startOffset, $dom->endOffset,
                $component,
                $props,
                [],
                $parent,
            );

            $slots = [];
            if ($inner = $dom->inner)
            {
                foreach ($inner as $i => $token)
                {
                    if ($token instanceof Tokens\_DomToken && $token->name === 'Slot')
                    {
                        $name = collect($token->props)->firstWhere('name', 'name')?->value;

                        if (is_null($name))
                        {
                            $token->syntaxError("Slot should contains [name] prop");
                        }

                        if (is_bool($name))
                        {
                            $token->syntaxError("Slot [name] prop should have a value");
                        }

                        $slots[] = new Structs\_DomSlotStruct(
                            $token->reader,
                            $token->startOffset, $token->endOffset,
                            $this->convertValue($name),
                            $this->convertNodes($token->inner, $node),
                            $node,
                        );

                        unset($inner[$i]);
                    }
                }
            }

            if (!in_array('slot', Arr::pluck($slots, 'name')) && $inner)
            {
                array_unshift($slots, new Structs\_DomSlotStruct(
                    $dom->reader,
                    $dom->startOffset, $dom->endOffset,
                    'slot',
                    $this->convertNodes($inner, $node),
                    $node,
                ));
            }

            $node->slots = $slots;

            return $node;
        }

        return null;
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