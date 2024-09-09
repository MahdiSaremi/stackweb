<?php

namespace StackWeb\Compilers\CliPhp;

use PhpParser\Error;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Return_;
use PhpParser\ParserFactory;
use StackWeb\Compilers\StringReader;
use StackWeb\Compilers\SyntaxError;

class CliPhpStaticTokenizer
{

    public static $escapes = ['"', "'"];

    public static function read(StringReader $string): Tokens\_CliPhpToken
    {
        $offset1 = $string->offset;
        $startLine = $string->getLine();
        $php = $string->readRange('{', '}', self::$escapes);

        return new Tokens\_CliPhpToken($string, $offset1, $string->offset, $php, static::parseExpr($php, $string, $offset1, $startLine));
    }

    private static function parseExpr(string $php, StringReader $string, int $start, int $startLine) : Expr
    {
        $additive = 14; // strlen("<?php return (");

        try
        {
            $parser = (new ParserFactory)->createForHostVersion();
            $stmts = $parser->parse("<?php return ($php);");
        }
        catch (Error $error)
        {
            throw new SyntaxError(
                sprintf("%s on line %s in [%s]", $error->getRawMessage(), $startLine + $error->getStartLine() - 1, $string->fileName),
            );
        }

        if (count($stmts) > 1)
        {
            $string->syntaxErrorAt(
                $stmts[1]->getStartTokenPos() + $start - $additive,
                "Expected inline variable"
            );
        }

        if (!($stmts[0] instanceof Return_) || !$stmts[0]->expr)
        {
            $string->syntaxErrorAt(
                $stmts[0]->getStartTokenPos() + $start - $additive,
                "Expected inline variable"
            );
        }

        return $stmts[0]->expr;
    }

}