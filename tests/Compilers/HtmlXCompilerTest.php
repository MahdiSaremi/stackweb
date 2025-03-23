<?php

namespace StackWeb\Tests\Compilers;

use StackWeb\Compilers\HtmlX\Tokenizer;
use StackWeb\Compilers\StringReader;
use StackWeb\Tests\TestCase;

class HtmlXCompilerTest extends TestCase
{

    public function test_0()
    {
        $tokenizer = new \StackWeb\Compilers\Stack\Tokenizer(new StringReader(
            <<<'Html'
            <input type="text">
            <div>
                <div class="text-error">Error <b>occurred</b></div>
            </div>
            Html,
            'test.php',
        ));

        $tokenizer->parse();
        dd($tokenizer->getTokens());
    }

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
            HtmlX,
            'test.php',
        ));

        $tokenizer->parse();
        dd($tokenizer->getTokens());
    }

}