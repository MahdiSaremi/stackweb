<?php

namespace StackWeb\Tests\Renderer;

use StackWeb\Renderer\Builder\StringBuilder;
use StackWeb\Tests\TestCase;

class StringBuilderTest extends TestCase
{

    public function test_simple_string()
    {
        $str = new StringBuilder();

        $str->append("Foo");

        $this->assertSame("'Foo'", $str->toCode());
    }

    public function test_merge_multiple_strings()
    {
        $str = new StringBuilder();

        $str->append("Foo");
        $str->append("Bar");

        $this->assertSame("'FooBar'", $str->toCode());
    }

    public function test_append_code_in_strings()
    {
        $str = new StringBuilder();

        $str->append("Foo");
        $str->appendCode("foo()");
        $str->append("Bar");

        $this->assertSame("'Foo'.foo().'Bar'", $str->toCode());
    }

    public function test_append_objects_in_strings()
    {
        $str = new StringBuilder();

        $str->appendObject("String");
        $str->appendObject(true);
        $str->appendObject(false); // false = ''
        $str->appendObject(null); // null = ''
        $str->appendObject([7]);
        $str->appendObject(9);

        $this->assertSame("'String1'.[7].'9'", $str->toCode());
    }

    public function test_prepend_strings()
    {
        $str = new StringBuilder();

        $str->append("Foo");
        $str->prepend("Bar");

        $this->assertSame("'BarFoo'", $str->toCode());
    }

    public function test_prepend_code_in_strings()
    {
        $str = new StringBuilder();

        $str->append("Foo");
        $str->prependCode('foo()');
        $str->prepend("Bar");
        $str->append('Test');

        $this->assertSame("'Bar'.foo().'FooTest'", $str->toCode());
    }

    public function test_prepend_objects_in_strings()
    {
        $str = new StringBuilder();

        $str->prependObject("String");
        $str->prependObject(true);
        $str->prependObject(false); // false = ''
        $str->prependObject(null); // null = ''
        $str->prependObject([7]);
        $str->prependObject(9);

        $this->assertSame("'9'.[7].'1String'", $str->toCode());
    }

}