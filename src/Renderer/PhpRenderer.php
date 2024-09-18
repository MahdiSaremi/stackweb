<?php

namespace StackWeb\Renderer;

class PhpRenderer
{

    public static function render($object)
    {
        switch (gettype($object))
        {
            case 'string':
                return "'" . static::renderInString($object) . "'";

            case 'integer':
            case 'double':
                return (string) $object;

            case 'boolean':
                return $object ? 'true' : 'false';

            case 'array':
                $result = '';
                $i = 0;
                foreach ($object as $key => $value)
                {
                    if ($result !== '')
                    {
                        $result .= ", ";
                    }

                    if (!is_int($key) || $key !== $i)
                    {
                        $result .= static::render($key) . " => " . static::render($value);
                        continue;
                    }

                    $result .= static::render($value);
                    $i++;
                }

                return "[$result]";

            case 'NULL':
                return 'null';

            default:
                return "unserialize(" . static::render(serialize($object)) . ")";
        }
    }

    public static function renderInString(string $inner)
    {
        return addcslashes($inner, "'\\");
    }

}