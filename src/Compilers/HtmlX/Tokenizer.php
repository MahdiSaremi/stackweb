<?php

namespace StackWeb\Compilers\HtmlX;

use StackWeb\Compilers\ApiPhp\ApiPhpStaticTokenizer;
use StackWeb\Compilers\Contracts\Tokenizer as TokenizerContract;
use StackWeb\Compilers\HtmlX\Tokens\_DynamicStringToken;
use StackWeb\Compilers\HtmlX\Tokens\_PreToken;
use StackWeb\Compilers\StringReader;

class Tokenizer implements TokenizerContract
{

    public array $tokens;

    public function __construct(
        protected StringReader $string,
    )
    {
    }

    public function parse(): void
    {
        $pre = $this->preParse();

        $tokens = [];
        /** @var Tokens\_PreToken $preToken */
        foreach ($pre as $preToken) {
            if ($preToken->type == Tokens\_PreToken::TEXT) {
                $tokens[] = new Tokens\_DomText($preToken->reader, $preToken->startOffset, $preToken->endOffset, $preToken->content);
            } elseif ($preToken->type == Tokens\_PreToken::OPEN) {
                $tokens[] = new Tokens\_DomToken(
                    $preToken->reader,
                    $preToken->startOffset,
                    $preToken->endOffset,
                    $preToken->content,
                    $preToken->props,
                    $preToken->selfClose,
                    null
                );
            } elseif ($preToken->type == Tokens\_PreToken::CLOSE) {
                for ($i = count($tokens) - 1; $i >= 0; $i--) {
                    if (
                        $tokens[$i] instanceof Tokens\_DomToken &&
                        !$tokens[$i]->selfClose &&
                        $tokens[$i]->inner === null &&
                        $tokens[$i]->name == $preToken->content
                    ) {
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
            } elseif ($preToken->type == Tokens\_PreToken::PHP) {
                $tokens[] = new Tokens\_PhpScriptToken(
                    $preToken->reader,
                    $preToken->startOffset,
                    $preToken->endOffset,
                    $preToken->content,
                );
            } elseif ($preToken->type == Tokens\_PreToken::PHP_EQ) {
                $tokens[] = new Tokens\_PhpEqScriptToken(
                    $preToken->reader,
                    $preToken->startOffset,
                    $preToken->endOffset,
                    $preToken->content,
                );
            } else {
                throw new \RuntimeException("Unhandled token type: " . $preToken->type);
            }
        }

        $this->tokens = $tokens;
    }

    public function preParse(): array
    {
        $string = $this->string;

        $tokens = [];

        $this->readWhiteSpaces($string, $tokens);
        while (!$string->end()) {
            $offset1 = $string->offset;
            $read = $string->read();

            if ($read == '<') {
                if ($string->readIf('/')) {
                    if ($tag = $string->readHWord()) {
                        $string->readWhiteSpaces();
                        if ($string->readIf('>')) {
                            $tokens[] = new Tokens\_PreToken($string, $offset1, $string->offset, Tokens\_PreToken::CLOSE, $tag);
                        } else {
                            $string->syntaxError("Expected '>'");
                        }
                    } else {
                        $this->appendText($string, $tokens, $read . $tag);
                    }
                } elseif ($tag = $string->readHWord()) {
                    [$props, $selfClose] = $this->parseProps($string);
                    $tokens[] = new Tokens\_PreToken($string, $offset1, $string->offset, Tokens\_PreToken::OPEN, $tag, $selfClose, $props);

                    if (!$selfClose) {
                        if ($tag == 'script') {
                            array_push($tokens, ...$this->parseScriptInner($string));
                        }
                    }
                } elseif ($string->readIf('?') && $phpOpen = $string->readIf(['php', '='])) {
                    $tokens[] = $this->parsePhpScriptTag($string, $phpOpen == 'php' ? Tokens\_PreToken::PHP : Tokens\_PreToken::PHP_EQ);
                } else {
                    $this->appendText($string, $tokens, $read . $tag);
                }
            } elseif ($read === '{' && $string->readIf('{')) {
                $tokens[] = new Tokens\_PreToken($string, $offset1, $string->offset, Tokens\_PreToken::TEXT, ApiPhpStaticTokenizer::read($string));
            } else {
                $this->appendText($string, $tokens, $read);
            }

            $this->readWhiteSpaces($string, $tokens);
        }

        return $tokens;
    }

    public function parseProps(StringReader $string): array
    {
        $props = [];

        $string->readWhiteSpaces();
        while (!$string->end()) {
            $start = $string->offset;
            $name = $string->readHWord();

            if ($name === '') {
                if ($string->readIf('{{')) {
                    $name = ApiPhpStaticTokenizer::read($string);
                }
            }

            if ($name !== '') {
                $string->readWhiteSpaces();
                if ($string->readIf('=')) {
                    $string->readWhiteSpaces();
                    if ($string->readIf('"')) {
                        $value = $this->readHtmlString($string, '"');
                    } elseif ($string->readIf("'")) {
                        $value = $this->readHtmlString($string, "'");
                    } elseif ($string->readIf('{{')) {
                        $value = ApiPhpStaticTokenizer::read($string);
                    } else {
                        $string->syntaxError("Expected ' or \" ");
                    }
                } else {
                    $value = true;
                }

                $props[] = new Tokens\_PropToken($string, $start, $string->offset, $name, $value);
            } elseif ($string->readIf('/>')) {
                return [$props, true];
            } elseif ($string->readIf('>')) {
                return [$props, false];
            } else {
                $string->syntaxError("Expected '>'");
            }

            $string->readWhiteSpaces();
        }

        $string->syntaxError("Expected '>'");
    }

    public function parseScriptInner(StringReader $string): array
    {
        $tokens = [];

        $inStringScope = false;

        while (!$string->end()) {
            $offset1 = $string->offset;
            $read = $string->read();

            if (!$inStringScope && $read == '<' && $string->readIf('/')) {
                $string->readWhiteSpaces();
                if ($string->readHWord() == 'script') {
                    $string->readWhiteSpaces();
                    if ($string->readIf('>')) {
                        $tokens[] = new Tokens\_PreToken($string, $offset1, $string->offset, Tokens\_PreToken::CLOSE, 'script');
                        break;
                    } else {
                        $string->syntaxError("Expected '>'");
                    }
                }

                $string->offset = $offset1 + 1;
            }

            if ($inStringScope && $read == "\\") {
                $this->appendText($string, $tokens, $read . $string->read());
            } elseif ($read == '"' || $read == "'" || $read == '`') {
                $inStringScope = $inStringScope ? false : $read;
                $this->appendText($string, $tokens, $read);
            } elseif ($read === '{' && $string->readIf('{')) {
                $tokens[] = new Tokens\_PreToken($string, $offset1, $string->offset, Tokens\_PreToken::TEXT, ApiPhpStaticTokenizer::read($string));
            } else {
                $this->appendText($string, $tokens, $read);
            }
        }

        return $tokens;
    }

    public function parsePhpScriptTag(StringReader $string, int $type): Tokens\_PreToken
    {
        $code = '';
        $inStringScope = false;
        $start = $string->offset;

        while (!$string->end()) {
            $offset1 = $string->offset;
            $read = $string->read();

            if (!$inStringScope && $read == '?' && $string->readIf('>')) {
                break;
            } elseif ($inStringScope && $read == "\\") {
                $code .= $read . $string->read();
            } elseif ($read == '"' || $read == "'") {
                $inStringScope = $inStringScope ? false : $read;
                $code .= $read;
            } else {
                $code .= $read;
            }
        }

        return new Tokens\_PreToken($string, $start, $string->offset, $type, $code);
    }

    public function appendText(StringReader $string, array &$tokens, string $text): void
    {
        if ($tokens &&
            end($tokens) instanceof Tokens\_PreToken &&
            end($tokens)->type == Tokens\_PreToken::TEXT &&
            is_string(end($tokens)->content)
        ) {
            end($tokens)->content .= $text;
            end($tokens)->endOffset = $string->offset;
        } else {
            $tokens[] = new Tokens\_PreToken($string, $string->offset - strlen($text), $string->offset, Tokens\_PreToken::TEXT, $text);
        }
    }

    public function readWhiteSpaces(StringReader $string, array &$tokens)
    {
        if ($string->readWhiteSpaces()) {
            $this->appendText($string, $tokens, ' ');
        }
    }

    public function readHtmlString(StringReader $string, string $char): string|_DynamicStringToken
    {
        $startOffset = $string->offset;
        if ($string->read(silent: true) === $char) {
            $string->read();
            return '';
        }

        $tokens = [];

        while (null !== $next = $string->read()) {
            if ($next == $char) {
                break;
            } elseif ($next == '{' && $string->readIf('{')) {
                $tokens[] = ApiPhpStaticTokenizer::read($string);
            } else {
                $this->appendText($string, $tokens, $next);
            }
        }

        if (count($tokens) == 0) {
            return '';
        }

        if (count($tokens) == 1 && $tokens[0]->type == Tokens\_PreToken::TEXT && is_string($tokens[0]->content)) {
            return $tokens[0]->content;
        }

        return new _DynamicStringToken($string, $startOffset, $string->offset, $tokens);
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }

}