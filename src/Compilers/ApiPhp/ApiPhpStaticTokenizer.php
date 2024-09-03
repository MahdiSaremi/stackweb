<?php

namespace StackWeb\Compilers\ApiPhp;

use StackWeb\Compilers\StringReader;
use StackWeb\Compilers\SyntaxError;

class ApiPhpStaticTokenizer
{

    public static $escapes = ['"', "'"];

    public static function read(StringReader $string): Tokens\_ApiPhpToken
    {
        $php = $string->readRange('{', '}', self::$escapes);

        if ($string->readIf('}'))
        {
            return new Tokens\_ApiPhpToken($php);
        }
        else
        {
            throw new SyntaxError("Expected '}}', given '}'");
        }
    }

}