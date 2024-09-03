<?php

namespace StackWeb\Tests\Compilers;

use StackWeb\Compilers\Stack\Tokenizer;
use StackWeb\Compilers\StringReader;
use StackWeb\Tests\TestCase;

class StackCompilerTest extends TestCase
{

    public function test_1()
    {
        $tokenizer = new Tokenizer(new StringReader(
            <<<'Stack'
            component Counter ($foo = null, $bar) {
                slot $icon {
                    <svg />
                }
                
                state $count = { 0 }
            
                render {
                    <div>Hello</div>
                }
            }
            Stack
        ));

        $tokenizer->parse();
        dd($tokenizer->getTokens());
    }

}