<?php

namespace StackWeb\Compilers\Stack;

use StackWeb\Compilers\Contracts\Compiler;
use StackWeb\Compilers\Stack\Structs\_StackStruct;
use StackWeb\Compilers\StringReader;

class StackCompiler implements Compiler
{
    public string $out;

    public function __construct(
        protected StringReader $string,
        public _StackStruct    $stack,
    )
    {
    }

    public static function from(StringReader $string, string $stackName): static
    {
        $parser = StackParser::from($string, $stackName);
        $parser->parse();

        return new static($string, $parser->getStruct());
    }

    public function compile()
    {

    }

    public function getOutput(): string
    {
        return $this->out;
    }
}