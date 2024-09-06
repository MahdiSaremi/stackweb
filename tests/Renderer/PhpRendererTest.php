<?php

namespace StackWeb\Tests\Renderer;

use StackWeb\Renderer\PhpRenderer;
use StackWeb\Tests\TestCase;

class PhpRendererTest extends TestCase
{

    public function test_string_rendering()
    {
        $this->assertSame("'Foo'", PhpRenderer::render('Foo'));
        $this->assertSame("'Foo\\'Bar'", PhpRenderer::render('Foo\'Bar'));
        $this->assertSame("'Foo\nBar'", PhpRenderer::render("Foo\nBar"));
    }

    public function test_builtin_types_rendering()
    {
        $this->assertSame("true", PhpRenderer::render(true));
        $this->assertSame("false", PhpRenderer::render(false));
        $this->assertSame("null", PhpRenderer::render(null));
        $this->assertSame("12", PhpRenderer::render(12));
        $this->assertSame("1.2", PhpRenderer::render(1.2));
    }

    public function test_array_rendering()
    {
        $this->assertSame("[]", PhpRenderer::render([]));
        $this->assertSame("[1, 2, 3]", PhpRenderer::render([1,2,3]));
        $this->assertSame("['foo' => 1, 'bar' => 2]", PhpRenderer::render(['foo' => 1, 'bar' => 2]));
        $this->assertSame("['1st', 'foo' => 1, '2nd', 'bar' => 2, '3rd']", PhpRenderer::render(['1st', 'foo' => 1, '2nd', 'bar' => 2, '3rd']));
    }

    public function test_object_rendering()
    {
        $object = new \stdClass();
        $object->foo = 'bar';

        $this->assertSame("unserialize('O:8:\"stdClass\":1:{s:3:\"foo\";s:3:\"bar\";}')", PhpRenderer::render($object));
    }

}