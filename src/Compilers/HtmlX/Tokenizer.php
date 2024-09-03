<?php

namespace StackWeb\Compilers\HtmlX;

use StackWeb\Compilers\ApiPhp\ApiPhpStaticTokenizer;
use StackWeb\Compilers\CliPhp\CliPhpStaticTokenizer;
use StackWeb\Compilers\Contracts\Tokenizer as TokenizerContract;
use StackWeb\Compilers\StringReader;
use StackWeb\Compilers\SyntaxError;

class Tokenizer implements TokenizerContract
{

    public array $tokens;

    public function __construct(
        protected StringReader $string,
    )
    {
    }

    public function parse() : void
    {
        $pre = $this->preParse();

        $tokens = [];
        foreach ($pre as $preToken)
        {
            if (is_string($preToken) || $preToken instanceof Tokens\_PropValue)
            {
                $tokens[] = new Tokens\_DomText($preToken);
            }
            elseif ($preToken[0] == 'open')
            {
                [, $name, $selfClose, $props] = $preToken;
                $tokens[] = new Tokens\_DomToken($name, $props, $selfClose, null);
            }
            elseif ($preToken[0] == 'close')
            {
                [, $name] = $preToken;
                for ($i = count($tokens) - 1; $i >= 0; $i--)
                {
                    if (
                        $tokens[$i] instanceof Tokens\_DomToken &&
                        !$tokens[$i]->selfClose &&
                        $tokens[$i]->inner === null &&
                        $tokens[$i]->name == $name
                    )
                    {
                        $cur = $tokens[$i];
                        $inner = array_splice($tokens, $i + 1);
                        $tokens[$i] = new Tokens\_DomToken(
                            $cur->name,
                            $cur->props,
                            false,
                            $inner,
                        );
                        continue 2;
                    }
                }
            }
        }

        $this->tokens = $tokens;
    }

    public function preParse() : array
    {
        $string = $this->string;

        $tokens = [];

        $this->readWhiteSpaces($string, $tokens);
        while (!$string->end())
        {
            $read = $string->read();

            if ($read == '<')
            {
                if ($string->readIf('/'))
                {
                    if ($tag = $string->readHWord())
                    {
                        $string->readWhiteSpaces();
                        if ($string->readIf('>'))
                        {
                            $tokens[] = ['close', $tag];
                        }
                        else
                        {
                            throw new SyntaxError("Expected '>'");
                        }
                    }
                    else
                    {
                        $this->appendText($tokens, $read . $tag);
                    }
                }
                elseif ($tag = $string->readHWord())
                {
                    [$props, $selfClose] = $this->parseProps($string);
                    $tokens[] = ['open', $tag, $selfClose, $props];
                }
                else
                {
                    $this->appendText($tokens, $read . $tag);
                }
            }
            elseif ($read === '{')
            {
                if ($string->readIf('{'))
                {
                    $tokens[] = ApiPhpStaticTokenizer::read($string);
                }
                else
                {
                    $tokens[] = CliPhpStaticTokenizer::read($string);
                }
            }
            else
            {
                $this->appendText($tokens, $read);
            }

            $this->readWhiteSpaces($string, $tokens);
        }

        return $tokens;
    }

    public function parseProps(StringReader $string) : array
    {
        $props = [];

        $string->readWhiteSpaces();
        while (!$string->end())
        {
            $name = $string->readHWord();

            if ($name === '')
            {
                if ($string->readIf('{{'))
                {
                    $name = ApiPhpStaticTokenizer::read($string);
                }
                elseif ($string->readIf('{'))
                {
                    $name = CliPhpStaticTokenizer::read($string);
                }
            }

            if ($name !== '')
            {
                $string->readWhiteSpaces();
                if ($string->readIf('='))
                {
                    $string->readWhiteSpaces();
                    if ($string->readIf('"'))
                    {
                        $value = $string->readEscape('"', translate: true);
                    }
                    elseif ($string->readIf("'"))
                    {
                        $value = $string->readEscape("'", translate: true);
                    }
                    elseif ($string->readIf('{{'))
                    {
                        $value = ApiPhpStaticTokenizer::read($string);
                    }
                    elseif ($string->readIf('{'))
                    {
                        $value = CliPhpStaticTokenizer::read($string);
                    }
                    else
                    {
                        throw new SyntaxError("Expected ' or \" ");
                    }
                }
                else
                {
                    $value = true;
                }

                $props[] = new Tokens\_PropToken($name, $value);
            }
            elseif ($string->readIf('/>'))
            {
                return [$props, true];
            }
            elseif ($string->readIf('>'))
            {
                return [$props, false];
            }
            else
            {
                throw new SyntaxError("Expected prop name");
            }

            $string->readWhiteSpaces();
        }

        throw new SyntaxError("Expected '>'");
    }

    public function appendText(array &$tokens, string $text)
    {
        if ($tokens && is_string(end($tokens)))
        {
            $tokens[count($tokens) - 1] .= $text;
        }
        else
        {
            $tokens[] = $text;
        }
    }

    public function readWhiteSpaces(StringReader $string, array &$tokens)
    {
        if ($string->readWhiteSpaces())
        {
            $this->appendText($tokens, ' ');
        }
    }

    public function getTokens() : array
    {
        return $this->tokens;
    }

}