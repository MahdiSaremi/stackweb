<?php

namespace StackWeb\Tests\Compilers;

use StackWeb\Compilers\StringReader;
use StackWeb\Tests\TestCase;

class StringReaderTest extends TestCase
{

    public function test_read()
    {
        $string = new StringReader("Hello World");

        $this->assertSame('H', $string->read());
        $this->assertSame('e', $string->read());
        $this->assertSame('ll', $string->read(2));

        $this->assertSame('o', $string->read(silent: true));
        $this->assertSame('o', $string->read());
        $this->assertSame(' ', $string->read());

        $this->assertSame('World', $string->read(10, silent: true));
        $this->assertSame(null, $string->read(10, forceLength: true, silent: true));

        $string->offset = 9999;
        $this->assertSame(null, $string->read());
    }

    public function test_simple_read_while()
    {
        $string = new StringReader("1234,56789");

        $res = $string->readWhile(
            fn ($value) => is_numeric($value),
            breaker: $breaker,
            broken: $broken,
        );

        $this->assertSame('1234', $res);
        $this->assertSame(4, $string->offset);
        $this->assertSame(',', $breaker);
        $this->assertSame(true, $broken);


        $res = $string->readWhile(fn ($value) => is_numeric($value));
        $this->assertSame('', $res);


        $string->read();

        $res = $string->readWhile(
            fn ($value) => is_numeric($value),
            breaker: $breaker,
            broken: $broken,
        );

        $this->assertSame('56789', $res);
        $this->assertSame(null, $breaker);
        $this->assertSame(false, $broken);
    }

    public function test_read_while_steps()
    {
        $string = new StringReader("12345,9-5!");

        $res = $string->readWhile(
            fn ($value) => is_numeric($value),
            step: 2,
        );
        $this->assertSame('1234', $res);

        $string->offset = 0;
        $res = $string->readWhile(
            fn ($value) => is_numeric($value[0]),
            step: 2,
        );
        $this->assertSame('12345,9-5!', $res);
    }

    public function test_read_while_jumps()
    {
        $string = new StringReader("12345,9-!");

        $res = $string->readWhile(
            fn ($value) => is_numeric($value),
            step: 2,
            jump: 1
        );
        $this->assertSame('12345', $res);

        $string->offset = 0;
        $res = $string->readWhile(
            fn ($value) => is_numeric($value[0]),
            jump: 2,
        );
        $this->assertSame('1359', $res);
    }

    public function test_read_while_options()
    {
        $string = new StringReader("12,34");

        $res = $string->readWhile(
            fn ($value) => is_numeric($value),
            skipBreaker: true,
            includeBreaker: true,
        );
        $this->assertSame('12,', $res);
        $this->assertSame(3, $string->offset);
    }


    public function test_simple_read_until()
    {
        $string = new StringReader("1234,56789");

        $res = $string->readUntil(
            fn ($value) => $value == ',',
            breaker: $breaker,
            broken: $broken,
        );

        $this->assertSame('1234', $res);
        $this->assertSame(4, $string->offset);
        $this->assertSame(',', $breaker);
        $this->assertSame(true, $broken);


        $res = $string->readUntil(fn ($value) => $value == ',');
        $this->assertSame('', $res);


        $string->read();

        $res = $string->readUntil(
            fn ($value) => $value == ',',
            breaker: $breaker,
            broken: $broken,
        );

        $this->assertSame('56789', $res);
        $this->assertSame(null, $breaker);
        $this->assertSame(false, $broken);
    }

    public function test_silent_process()
    {
        $string = new StringReader("Hello World");
        $string->offset = 6;

        $read = $string->silent(fn() => $string->read(100));

        $this->assertSame('World', $read);
        $this->assertSame(6, $string->offset);
    }

    public function test_read_escape()
    {
        $string = new StringReader('Hi"');
        $this->assertSame('Hi', $string->readEscape('"'));

        $string = new StringReader('Hi\\" Foo \\n Bar " Exclude');
        $this->assertSame('Hi\\" Foo \\n Bar ', $string->readEscape('"'));
    }

    public function test_read_range()
    {
        $string = new StringReader('Range}');
        $this->assertSame('Range', $string->readRange('{', '}'));

        $string = new StringReader('Range {Deep} }');
        $this->assertSame('Range {Deep} ', $string->readRange('{', '}'));

        $string = new StringReader('Range {Deep "String}}" } "String}}" }');
        $this->assertSame('Range {Deep "String}}" } "String}}" ', $string->readRange('{', '}', ['"']));

        // Without escapes:
        $string->offset = 0;
        $this->assertSame('Range {Deep "String}', $string->readRange('{', '}'));

        $string = new StringReader('This {Is "A }\\"} So}"} "Complex } \\"} String} {}" ! {Amazing} }');
        $this->assertSame('This {Is "The }\\"} So}"} "Complex } \\"} String} {}" ! {Amazing} ', $string->readRange('{', '}', ['"']));
    }

    public function test_read_trig()
    {
        $string = new StringReader('A,B');
        $this->assertSame('A', $string->readTrig(','));

        $string = new StringReader('A "S,T,R" B,');
        $this->assertSame('A "S,T,R" B', $string->readTrig(',', ['"']));

        $string->offset = 0;
        $this->assertSame('A "S', $string->readTrig(','));

        $string = new StringReader('Foo { ,, } "," { "," } ,');
        $this->assertSame('Foo { ,, } "," { "," } ', $string->readTrig(',', ['"'], [['{', '}', ['"']]]));
    }

}