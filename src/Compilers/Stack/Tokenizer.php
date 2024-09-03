<?php

namespace StackWeb\Compilers\Stack;

use StackWeb\Compilers\Contracts\Tokenizer as TokenizerContract;
use StackWeb\Compilers\StringReader;
use StackWeb\Compilers\SyntaxError;

class Tokenizer implements TokenizerContract
{

    public function __construct(
        protected StringReader $string,
    )
    {
    }

    public array $escapes = ['"', "'"];
    public array $ranges = [
        ['{', '}', ['"', "'"]],
        ['(', ')', ['"', "'"]],
        ['[', ']', ['"', "'"]],
    ];

    protected array $tokens;

    public function parse() : void
    {
        $this->tokens = [];

        $string = $this->string;

        $string->readWhiteSpaces();
        while (!$string->end())
        {
            if ('' !== $word = $string->readCWord())
            {
                switch (strtolower($word))
                {
                    case 'component':
                        $this->tokens[] = $this->parseComponent($string);
                        break;

                    default:
                        throw new SyntaxError("Unknown symbol [$word]");
                }
            }
            else
            {
                throw new SyntaxError("Syntax error");
            }

            $string->readWhiteSpaces();
        }
    }

    public function parseComponent(StringReader $string) : Tokens\_ComponentToken
    {
        $string->readWhiteSpaces();
        if ('' === $name = $string->readCWord())
        {
            $name = null;
        }

        $string->readWhiteSpaces();
        $props = [];
        if ($string->readIf('('))
        {
            $inner = new StringReader($string->readRange('(', ')', $this->escapes));
            $props = $this->parseComponentProps($inner);
        }

        $string->readWhiteSpaces();
        if ($string->readIf('{'))
        {
            $inner = new StringReader($string->readRange('{', '}', $this->escapes));
            $tokens = $this->parseComponentInner($inner);

            return new Tokens\_ComponentToken($name, $props, $tokens);
        }
        else
        {
            throw new SyntaxError("Expected '{'");
        }
    }

    public function parseComponentProps(StringReader $string) : array
    {
        $props = [];
        $string->readWhiteSpaces();
        while (!$string->end())
        {
            if ($string->readIf('$'))
            {
                if (!$name = $string->readCWord())
                {
                    throw new SyntaxError("Expected prop name");
                }
            }
            else throw new SyntaxError("Expected prop name");

            $string->readWhiteSpaces();

            $default = null;
            if ($string->readIf('='))
            {
                $string->readWhiteSpaces();
                $default = $string->readTrig(',', $this->escapes, $this->ranges, skipBreaker: false);
            }

            $string->readWhiteSpaces();

            if (!$string->end() && !$string->readIf(','))
            {
                throw new SyntaxError("Expected ','");
            }

            $props[] = new Tokens\_ComponentPropToken($name, $default);

            $string->readWhiteSpaces();
        }

        return $props;
    }

    public function parseComponentInner(StringReader $string) : array
    {
        $tokens = [];

        $string->readWhiteSpaces();
        while (!$string->end())
        {
            ... // TODO

            $string->readWhiteSpaces();
        }

        return $tokens;
    }

    public function getTokens() : array
    {
        return $this->tokens;
    }

}