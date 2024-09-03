<?php

namespace StackWeb\Compilers\CliPhp;

use StackWeb\Compilers\StringReader;
use StackWeb\Compilers\SyntaxError;

class CliPhpStaticTokenizer
{

    public static $escapes = ['"', "'"];

    public static function read(StringReader $string): Tokens\_CliPhpToken
    {
        $php = $string->readRange('{', '}', self::$escapes);

        return new Tokens\_CliPhpToken($php);
    }

}