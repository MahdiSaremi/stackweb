<?php

namespace StackWeb\Compilers\ApiPhp;

use StackWeb\Compilers\StringReader;
use StackWeb\Compilers\SyntaxError;

class ApiPhpStaticTokenizer
{

    public static $escapes = ['"', "'"];

    public static function read(StringReader $string): Tokens\_ApiPhpToken
    {
        $offset1 = $string->offset;
        $php = $string->readRange('{', '}', self::$escapes);

        if ($string->readIf('}'))
        {
            return new Tokens\_ApiPhpToken($string, $offset1, $string->offset, $php);
        }
        else
        {
            $string->syntaxError("Expected '}}'");
        }
    }

}