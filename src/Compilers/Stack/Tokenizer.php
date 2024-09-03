<?php

namespace StackWeb\Compilers\Stack;

use StackWeb\Compilers\ApiPhp\ApiPhpStaticTokenizer;
use StackWeb\Compilers\CliPhp\CliPhpStaticTokenizer;
use StackWeb\Compilers\Contracts\Tokenizer as TokenizerContract;
use StackWeb\Compilers\StringReader;

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
            $offset1 = $string->offset;
            if ('' !== $word = $string->readCWord())
            {
                switch (strtolower($word))
                {
                    case 'component':
                        $this->tokens[] = $this->parseComponent($string, $offset1);
                        break;

                    default:
                        $string->syntaxError("Unknown symbol [$word]");
                }
            }
            else
            {
                $string->syntaxError("Syntax error");
            }

            $string->readWhiteSpaces();
        }
    }

    public function parseComponent(StringReader $string, int $start) : Tokens\_ComponentToken
    {
        $string->readWhiteSpaces();
        if ('' === $name = $string->readCWord())
        {
            $name = null;
        }

        $string->readWhiteSpaces();
        $offset1 = $string->offset;
        $props = [];
        if ($string->readIf('('))
        {
            $line = $string->getLine();
            $index = $string->getIndex();
            $inner = new StringReader(
                $string->readRange('(', ')', $this->escapes),
                $string->fileName,
                $string,
                $line,
                $index,
            );
            $props = $this->parseComponentProps($inner, $offset1);
        }

        $string->readWhiteSpaces();
        if ($string->readIf('{'))
        {
            $line = $string->getLine();
            $index = $string->getIndex();
            $inner = new StringReader(
                $string->readRange('{', '}', $this->escapes),
                $string->fileName,
                $string,
                $line,
                $index,
            );
            $tokens = $this->parseComponentInner($inner);

            return new Tokens\_ComponentToken($string, $start, $string->offset, $name, $props, $tokens);
        }
        else
        {
            $string->syntaxError("Expected '{'");
        }
    }

    public function parseComponentProps(StringReader $string, int $start) : array
    {
        $props = [];
        $string->readWhiteSpaces();
        while (!$string->end())
        {
            if ($string->readIf('$'))
            {
                if (!$name = $string->readCWord())
                {
                    $string->syntaxError("Expected prop name");
                }
            }
            else $string->syntaxError("Expected prop name");

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
                $string->syntaxError("Expected ','");
            }

            $props[] = new Tokens\_ComponentPropToken($string, $start, $string->offset, $name, $default);

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
            $offset1 = $string->offset;
            $word = $string->readCWord();

            switch ($word)
            {
                case 'render':
                    $tokens[] = $this->parseRender($string, $offset1);
                    break;

                case 'slot':
                    $tokens[] = $this->parseSlot($string, $offset1);
                    break;

                case 'state':
                    $tokens[] = $this->parseState($string, $offset1);
                    break;

                default:
                    $string->syntaxError("Unexpected symbol [$word]");
            }

            $string->readWhiteSpaces();
        }

        return $tokens;
    }

    public function parseRender(StringReader $string, int $start) : Tokens\_ComponentRenderToken
    {
        $string->readWhiteSpaces();
        if ($string->readIf('{'))
        {
            $line = $string->getLine();
            $index = $string->getIndex();
            $inner = new StringReader(
                $string->readRange('{', '}', $this->escapes),
                $string->fileName,
                $string,
                $line,
                $index,
            );

            $htmlX = new \StackWeb\Compilers\HtmlX\Tokenizer($inner);
            $htmlX->parse();

            return new Tokens\_ComponentRenderToken($string, $start, $string->offset, $htmlX->getTokens());
        }
        else
        {
            $string->syntaxError("Expected '{'");
        }
    }

    public function parseSlot(StringReader $string, int $start) : Tokens\_ComponentSlotToken
    {
        $string->readWhiteSpaces();
        if ($string->readIf('$') && $name = $string->readCWord())
        {
            $string->readWhiteSpaces();
            $default = null;
            if ($string->readIf('{'))
            {
                $line = $string->getLine();
                $index = $string->getIndex();
                $inner = new StringReader(
                    $string->readRange('{', '}', $this->escapes),
                    $string->fileName,
                    $string,
                    $line,
                    $index,
                );

                $htmlX = new \StackWeb\Compilers\HtmlX\Tokenizer($inner);
                $htmlX->parse();

                $default = $htmlX->getTokens();
            }

            return new Tokens\_ComponentSlotToken($string, $start, $string->offset, $name, $default);
        }
        else
        {
            $string->syntaxError("Expected slot name");
        }
    }

    public function parseState(StringReader $string, int $start) : Tokens\_ComponentStateToken
    {
        $string->readWhiteSpaces();
        if ($string->readIf('$') && $name = $string->readCWord())
        {
            $string->readWhiteSpaces();
            $default = null;
            if ($string->readIf('='))
            {
                $string->readWhiteSpaces();
                if ($string->readIf('{{'))
                {
                    $default = ApiPhpStaticTokenizer::read($string);
                }
                elseif ($string->readIf('{'))
                {
                    $default = CliPhpStaticTokenizer::read($string);
                }
                else
                {
                    $string->syntaxError("This syntax coming soon..."); // state $x = 0
                }
            }

            return new Tokens\_ComponentStateToken($string, $start, $string->offset, $name, $default);
        }
        else
        {
            $string->syntaxError("Expected slot name");
        }
    }

    public function getTokens() : array
    {
        return $this->tokens;
    }

}