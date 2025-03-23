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

    public static function from(StringReader $string): static
    {
        $parser = StackParser::from($string);
        $parser->parse();

        return new static($string, $parser->getStruct());
    }

    public function compile()
    {
        $this->out = "<?php\n" . $this->stack->phpInitializer . "\n\n";
        $this->out .= sprintf(
            "",
            Component
        );
    }

    public function getOutput(): string
    {
        return $this->out;
    }
}