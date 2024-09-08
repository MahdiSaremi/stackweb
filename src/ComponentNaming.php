<?php

namespace StackWeb;

use Illuminate\Support\Str;

class ComponentNaming
{

    public static function viewToComponent(string $view)
    {
        return preg_replace_callback(
            '/(^|[:._\-\s])([a-z])/',
            function($match)
            {
                return (in_array($match[1], ['_', '-']) ? '' : $match[1]) . strtoupper($match[2]);
            },
            $view
        );
    }

    public static function componentToView(string $component)
    {
        [$namespace, $component] = static::splitComponent($component);

        if (isset($namespace))
        {
            $namespace = preg_replace_callback('/(^|[a-z:.])([A-Z])/', function ($match)
            {
                return (in_array($match[1], [':', '.', '']) ? $match[1] : $match[1] . '-') . strtolower($match[2]);
            }, $namespace);
        }

        $component = preg_replace_callback('/(^|[a-z:.])([A-Z])/', function ($match)
        {
            return (in_array($match[1], [':', '.', '']) ? $match[1] : $match[1] . '-') . strtolower($match[2]);
        }, $component);

        return (isset($namespace) ? $namespace . '::' : '') . $component;
    }


    public static function splitComponent(string $component)
    {
        $namespace = null;
        $subject = null;

        if (str_contains($component, "::"))
        {
            [$namespace, $component] = explode('::', $component, 2);
        }

        if (str_contains($component, ":"))
        {
            $subject = Str::afterLast($component, ":");
            $component = Str::beforeLast($component, ":");
        }

        return [$namespace, $component, $subject];
    }

    public static function implodeComponent(?string $namespace, string $component, ?string $subject)
    {
        return
            (isset($namespace) ? $namespace . '::' : '') .
            $component .
            (isset($subject) ? ':' . $subject : '');
    }

}