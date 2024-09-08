<?php

namespace StackWeb\Tests\Renderer;

use StackWeb\Renderer\DomRenderer;
use StackWeb\Tests\TestCase;

class DomRendererTest extends TestCase
{

    public function test_render_html()
    {
        $this->assertSame(
            'foobar',
            DomRenderer::render(['foo', 'bar']),
        );
    }

    public function test_render_dom()
    {
        $this->assertSame(
            '<div>Hello World</div>',
            DomRenderer::render([['dom', 'div', [], ['Hello World']]]),
        );

        $this->assertSame(
            '<div id="123" class="bg-white" enabled><span>Foo</span> <span>Bar</span></div>',
            DomRenderer::render([
                ['dom', 'div', ['id' => '123', 'class' => 'bg-white', 'enabled' => true, 'hidden' => false], [
                    ['dom', 'span', [], ['Foo']],
                    ' ',
                    ['dom', 'span', [], ['Bar']],
                ]],
            ]),
        );
    }

    public function test_render_components()
    {
        
    }

}