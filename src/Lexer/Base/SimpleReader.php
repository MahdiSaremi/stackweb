<?php

namespace StackWeb\Lexer\Base;

use Closure;

class SimpleReader
{

    public int $length;

    public function __construct(
        public string $string,
        public int $offset = 0,
    )
    {
        $this->length = strlen($string);
    }

    public function has(int $count = 1)
    {
        return $this->offset + $count <= $this->length;
    }

    public function end()
    {
        return ! $this->has();
    }


    public function get()
    {
        return $this->string[$this->offset] ?? false;
    }

    public function read()
    {
        if ($this->end())
        {
            return false;
        }

        return $this->string[$this->offset++];
    }

    public function readIf(string $word)
    {
        if (
            $this->has(strlen($word)) &&
            $this->string[$this->offset] == $word[0] &&
            (strlen($word) == 1 || substr($this->string, $this->offset + 1, strlen($word) - 1) == substr($word, 1))
        )
        {
            $this->offset += strlen($word);
            return true;
        }

        return false;
    }

    public function readWhile(callable $condition)
    {
        $string = '';
        while ($this->has() && $condition($this->string[$this->offset]))
        {
            $string .= $this->string[$this->offset];
            $this->offset++;
        }

        return $string;
    }

    public function readUntil(callable $condition)
    {
        $string = '';
        while ($this->has() && ! $condition($this->string[$this->offset]))
        {
            $string .= $this->string[$this->offset];
            $this->offset++;
        }

        return $string;
    }

    public function readWord()
    {
        static $valid1 = [...range('a', 'z'), ...range('A', 'Z'), '_'];
        static $valid2 = [...range('a', 'z'), ...range('A', 'Z'), ...range('0', '9'), '_'];

        if (!in_array($this->get(), $valid1))
        {
            return '';
        }

        return $this->read() . $this->readWhile(fn ($x) => in_array($x, $valid2));
    }


    public function skip(callable $condition)
    {
        while ($this->has() && $condition($this->string[$this->offset]))
        {
            $this->offset++;
        }
    }

    public function skipWhitespace()
    {
        $this->skip('ctype_space');
    }

}