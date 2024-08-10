<?php

namespace StackWeb\Lexer\Js;

class JsLexer
{

    public function __construct(
        protected JsReader $js,
    )
    {
        $this->parse();
    }

    public array $tokens;

    protected function parse()
    {
        $result = [];

        $js = $this->js;
        while ($js->has())
        {
            $js->skipWhitespace();

            if ($js->readIf('let ') || $js->readIf('var '))
            {
                $js->skipWhitespace();

                if ($word = $js->readWord())
                {
                    $js->skipWhitespace();
                    if ($js->readIf('='))
                    {
                        $js->skipWhitespace();
                        unset($lastLine);
                        $lastLine = $js->readLine();

                        $result[] = [
                            'type' => 'var',
                            'name' => $word,
                            'value' => &$lastLine,
                        ];
                    }
                    else
                    {
                        die("Error");
                    }
                }
                else
                {
                    die("Error");
                }
            }
            elseif ($word = $js->readWord())
            {
                $js->skipWhitespace();
                $async = false;

                if ($word == 'async')
                {
                    $async = true;
                    $js->skipWhitespace();
                    $word = $js->readWord();
                }

                if ($word == 'function')
                {
                    $js->skipWhitespace();
                    if ($word = $js->readWord())
                    {
                        unset($lastLine);

                        $result[] = [
                            'type' => 'function',
                            'name' => $word,
                            'async' => $async,
                            'value' => $js->readLine(funcMode: true),
                        ];
                    }
                    else
                    {
                        die("Error");
                    }
                }
                elseif ($async)
                {
                    die("Error");
                }
                else
                {
                    if (isset($lastLine))
                    {
                        $lastLine .= $word;
                    }
                    else
                    {
                        die("Error");
                    }
                }
            }
            elseif ($js->readIf(';'))
            {
                if (isset($lastLine))
                {
                    $lastLine .= ';';
                    unset($lastLine);
                }
            }
            else
            {
                unknown:
                if (isset($lastLine))
                {
                    $lastLine .= $js->read();
                }
                else
                {
                    die("Error");
                }
            }
        }

        $this->tokens = $result;
    }

}