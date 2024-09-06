<?php

namespace StackWeb\Renderer\Builder;

use StackWeb\Renderer\PhpRenderer;

class StringBuilder
{

    protected array $strings = [];

    public function append(string $string)
    {
        if (count($this->strings) && end($this->strings)[0] == 'string')
        {
            $this->strings[count($this->strings) - 1][1] .= $string;
            return $this;
        }

        $this->strings[] = ['string', $string];
        return $this;
    }

    public function appendObject($object)
    {
        if (is_string($object) || is_int($object) || is_double($object))
        {
            return $this->append((string) $object);
        }
        elseif (is_bool($object))
        {
            return $this->append($object ? '1' : '');
        }
        elseif (is_null($object))
        {
            return $this;
        }

        $this->strings[] = ['object', $object];
        return $this;
    }

    public function appendCode(string $php, bool $addParenthesis = false)
    {
        if ($addParenthesis)
        {
            $php = "($php)";
        }

        $this->strings[] = ['code', $php];
        return $this;
    }

    public function prepend(string $string)
    {
        if (count($this->strings) && $this->strings[0][0] == 'string')
        {
            $this->strings[0][1] = $string . $this->strings[0][1];
            return $this;
        }

        array_unshift($this->strings, ['string', $string]);
        return $this;
    }

    public function prependObject($object)
    {
        if (is_string($object) || is_int($object) || is_double($object))
        {
            return $this->prepend((string) $object);
        }
        elseif (is_bool($object))
        {
            return $this->prepend($object ? '1' : '');
        }
        elseif (is_null($object))
        {
            return $this;
        }

        array_unshift($this->strings, ['object', $object]);
        return $this;
    }

    public function prependCode(string $php, bool $addParenthesis = false)
    {
        if ($addParenthesis)
        {
            $php = "($php)";
        }

        array_unshift($this->strings, ['code', $php]);
        return $this;
    }


    public function toCode()
    {
        $result = "";
        foreach ($this->strings as [$type, $value])
        {
            if ($result !== "")
            {
                $result .= ".";
            }

            if ($type == 'code')
            {
                $result .= $value;
            }
            else
            {
                $result .= PhpRenderer::render($value);
            }
        }

        return $result === '' ? "''" : $result;
    }

}