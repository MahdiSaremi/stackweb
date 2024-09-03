<?php

namespace StackWeb\Tests\Compilers;

use StackWeb\Compilers\HtmlX\Tokenizer;
use StackWeb\Compilers\StringReader;
use StackWeb\Tests\TestCase;

class HtmlXCompilerTest extends TestCase
{

    public function test_1()
    {
        $tokenizer = new Tokenizer(new StringReader(
            <<<'HtmlX'
                <div
                    id={ $id }
                    name={{ $name }}
                    { $a }={ $b }
                    {{ $a }}={{ $b }}
                />
                <div>
                    <a href="#">Home { $text } {{ user()->id }}</a>
                </div>
                <div />
            HtmlX
        ));

        $tokenizer->parse();
        dd($tokenizer->getTokens());
    }

}