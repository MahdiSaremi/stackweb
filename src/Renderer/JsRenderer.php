<?php

namespace StackWeb\Renderer;

use Illuminate\Support\Js;
use StackWeb\Compilers\ApiPhp\Structs\_ApiPhpStruct;
use StackWeb\Compilers\CliPhp\Structs\_CliPhpStruct;
use StackWeb\Renderer\Builder\StringBuilder;
use StackWeb\Renderer\Contracts\SourceRenderer;

class JsRenderer
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
                if (array_is_list($object))
                {
                    return '[' . implode(',', array_map(static::render(...), $object)) . ']';
                }
                else
                {
                    $result = '';
                    foreach ($object as $key => $value)
                    {
                        if ($result !== '')
                        {
                            $result .= ",";
                        }

                        $result .= preg_match('/^[a-zA-Z$_][a-zA-Z$0-9_]*$/', $key) ? $key : static::render($key);

                        $result .= ':' . static::render($value);
                    }

                    return "{$result}";
                }

            case 'NULL':
                return 'null';

            default:
                return Js::encode($object);
        }
    }

    public static function renderInString(string $inner)
    {
        return addcslashes($inner, "'");
    }

    public static function renderIn(SourceRenderer $renderer, StringBuilder $out, $object)
    {
        if (is_array($object))
        {
            if (array_is_list($object))
            {
                $out->append('[');
                foreach ($object as $i => $value)
                {
                    if ($i > 0)
                    {
                        $out->append(',');
                    }

                    static::renderIn($renderer, $out, $value);
                }
                $out->append(']');
            }
            else
            {
                $out->append('{');
                $isFirst = true;
                foreach ($object as $key => $value)
                {
                    if ($isFirst)
                    {
                        $out->append(',');
                        $isFirst = false;
                    }

                    $out->append(preg_match('/^[a-zA-Z$_][a-zA-Z$0-9_]*$/', $key) ? $key : static::render($key));

                    $out->append(':');
                    static::renderIn($renderer, $out, $value);
                }
                $out->append('}');
            }
        }
        elseif ($object instanceof _CliPhpStruct)
        {
            $out->appendCode($object->js);
        }
        elseif ($object instanceof _ApiPhpStruct)
        {
            $renderer->renderCliGetApiResult($out, $object);
        }
        else
        {
            $out->append(static::render($object));
        }
    }

}