<?php

namespace StackWeb\Lexer\Js;

use StackWeb\Lexer\Base\SimpleReader;

class JsReader extends SimpleReader
{

    public function readLine(bool $funcMode = false)
    {
        $deepCount = 0;
        $anyDeepCount = 0;
        $stringType = null;
        $acceptNext = false;
        $finish = false;
        $startWithAc = false;

        return $this->readWhile(function ($x) use(&$deepCount, &$anyDeepCount, &$stringType, &$acceptNext, &$finish, $funcMode)
        {
            if ($acceptNext) return true;
            if ($finish) return false;

            if (isset($stringType))
            {
                if ($x == '\\')
                {
                    $acceptNext = true;
                }
                elseif ($x == $stringType)
                {
                    $stringType = null;
                }
            }
            elseif ($x == "'" || $x == '"' || $x == '`')
            {
                $stringType = $x;
            }
            elseif ($x == '{')
            {
                $deepCount++;
                $anyDeepCount++;
            }
            elseif ($x == '}')
            {
                $deepCount--;
                $anyDeepCount--;

                if ($anyDeepCount == 0 && $funcMode)
                {
                    $finish = true;
                }
            }
            elseif ($x == ';' && $deepCount == 0)
            {
                $finish = true;
            }
            elseif ($x == "\n")
            {
                if ($anyDeepCount == 0 && !$funcMode)
                {
                    $finish = true;
                }
            }
            elseif ($x == '[' || $x == '(')
            {
                $anyDeepCount++;
            }
            elseif ($x == ']' || $x == ')')
            {
                $anyDeepCount--;
            }

            return true;
        });
    }

}