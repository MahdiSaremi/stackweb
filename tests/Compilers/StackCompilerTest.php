<?php

namespace StackWeb\Tests\Compilers;

use StackWeb\Compilers\Stack\StackParser;
use StackWeb\Compilers\Stack\Tokenizer;
use StackWeb\Compilers\StringReader;
use StackWeb\Tests\TestCase;

class StackCompilerTest extends TestCase
{

    public function test_1()
    {
        $startAt = microtime(true);

        $tokenizer = StackParser::from(new StringReader(
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
            Stack,
            'test'
        ));

        $tokenizer->parse();

        $endAt = microtime(true);
        echo "Process in " . round(($endAt-$startAt)*1000, 3) . "ms\n";

        dd($tokenizer->getStruct());
    }

}