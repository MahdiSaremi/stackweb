<?php

namespace StackWeb\Tests\Renderer;

use StackWeb\Compilers\Stack\StackParser;
use StackWeb\Compilers\StringReader;
use StackWeb\Renderer\Builder\SourceBuilder;
use StackWeb\Renderer\SourceRendererDev;
use StackWeb\Tests\TestCase;

class SourceRendererTest extends TestCase
{

    public function test_main()
    {
        $startAt = microtime(true);

        $string = new StringReader(
            <<<'Stack'
            component Counter ($foo = null, $bar) {
                slot $icon {
                    <svg />
                }
                
                state $count = { 0 }
            
                render {
                    <div>Hello {{ $this->count }}</div>
                }
            }
            Stack,
            'test'
        );

        $parser = StackParser::from($string);
        $parser->parse();

        $out = new SourceBuilder();

        $renderer = new SourceRendererDev($string);
        $renderer->renderStack($out, $parser->getStruct());

        $endAt = microtime(true);
        echo "Process in " . round(($endAt-$startAt)*1000, 3) . "ms\n";

        dd($out->toCode());
    }

}