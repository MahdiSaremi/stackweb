<?php

namespace StackWeb\Renderer;

class DomRenderer
{

    public static function render(array $items)
    {
        $result = '';
        foreach ($items as $item)
        {
            if (is_string($item))
            {
                $result .= $item;
            }
            elseif (is_array($item))
            {
                switch ($item[0])
                {
                    case 'dom':
                        [, $name, $props, $slot] = $item;
                        $result .= "<$name" . static::renderProps($props);
                        if (is_null($slot))
                        {
                            $result .= "/>";
                        }
                        else
                        {
                            $result .= ">" . static::render($slot) . "</$name>";
                        }
                        break;
                }
            }
        }

        return $result;
    }

    public static function renderProps(array $props)
    {
        $result = '';
        foreach ($props as $key => $value)
        {
            if ($value === true)
            {
                $result .= ' ' . $key;
            }
            elseif ($value === false)
            {
                continue;
            }
            else
            {
                $result .= ' ' . $key . '="' . e($value) . '"';
            }
        }

        return $result;
    }

}