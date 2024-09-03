<?php

namespace StackWeb\Compilers\HtmlX;

use StackWeb\Compilers\ApiPhp\ApiPhpStaticTokenizer;
use StackWeb\Compilers\CliPhp\CliPhpStaticTokenizer;
use StackWeb\Compilers\Contracts\Tokenizer as TokenizerContract;
use StackWeb\Compilers\StringReader;

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
        /** @var Tokens\_PreToken $preToken */
        foreach ($pre as $preToken)
        {
            if ($preToken->type == 'text')
            {
                $tokens[] = new Tokens\_DomText($preToken->reader, $preToken->startOffset, $preToken->endOffset, $preToken->content);
            }
            elseif ($preToken->type == 'open')
            {
                $tokens[] = new Tokens\_DomToken(
                    $preToken->reader,
                    $preToken->startOffset,
                    $preToken->endOffset,
                    $preToken->content,
                    $preToken->props,
                    $preToken->selfClose,
                    null
                );
            }
            elseif ($preToken->type == 'close')
            {
                for ($i = count($tokens) - 1; $i >= 0; $i--)
                {
                    if (
                        $tokens[$i] instanceof Tokens\_DomToken &&
                        !$tokens[$i]->selfClose &&
                        $tokens[$i]->inner === null &&
                        $tokens[$i]->name == $preToken->content
                    )
                    {
                        $cur = $tokens[$i];
                        $inner = array_splice($tokens, $i + 1);
                        $tokens[$i] = new Tokens\_DomToken(
                            $cur->reader,
                            $cur->startOffset,
                            $cur->endOffset,
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
            $offset1 = $string->offset;
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
                            $tokens[] = new Tokens\_PreToken($string, $offset1, $string->offset, 'close', $tag);
                        }
                        else
                        {
                            $string->syntaxError("Expected '>'");
                        }
                    }
                    else
                    {
                        $this->appendText($string, $tokens, $read . $tag);
                    }
                }
                elseif ($tag = $string->readHWord())
                {
                    [$props, $selfClose] = $this->parseProps($string);
                    $tokens[] = new Tokens\_PreToken($string, $offset1, $string->offset, 'open', $tag, $selfClose, $props);
                }
                else
                {
                    $this->appendText($string, $tokens, $read . $tag);
                }
            }
            elseif ($read === '{')
            {
                if ($string->readIf('{'))
                {
                    $tokens[] = new Tokens\_PreToken($string, $offset1, $string->offset, 'text', ApiPhpStaticTokenizer::read($string));
                }
                else
                {
                    $tokens[] = new Tokens\_PreToken($string, $offset1, $string->offset, 'text', CliPhpStaticTokenizer::read($string));
                }
            }
            else
            {
                $this->appendText($string, $tokens, $read);
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
            $start = $string->offset;
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
                        $string->syntaxError("Expected ' or \" ");
                    }
                }
                else
                {
                    $value = true;
                }

                $props[] = new Tokens\_PropToken($string, $start, $string->offset, $name, $value);
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
                $string->syntaxError("Expected '>'");
            }

            $string->readWhiteSpaces();
        }

        $string->syntaxError("Expected '>'");
    }

    public function appendText(StringReader $string, array &$tokens, string $text)
    {
        if ($tokens && end($tokens)->type == 'text')
        {
            end($tokens)->content .= $text;
            end($tokens)->endOffset = $string->offset;
        }
        else
        {
            $tokens[] = new Tokens\_PreToken($string, $string->offset - strlen($text), $string->offset, 'text', $text);
        }
    }

    public function readWhiteSpaces(StringReader $string, array &$tokens)
    {
        if ($string->readWhiteSpaces())
        {
            $this->appendText($string, $tokens, ' ');
        }
    }

    public function getTokens() : array
    {
        return $this->tokens;
    }

}