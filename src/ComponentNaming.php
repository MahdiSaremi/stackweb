<?php

namespace StackWeb;

use Illuminate\Support\Str;

class ComponentNaming
{

    /**
     * Converts view name to component name
     *
     * `View: my-ns:foo-path.bar-name:subject`
     *
     * `Component: MyNs:FooPath.BarName:Subject`
     *
     * @param string $view
     * @return string
     */
    public static function viewToComponent(string $view) : string
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

    /**
     * Converts component name to view name
     *
     * `Component: MyNs:FooPath.BarName:Subject`
     *
     * `View: my-ns:foo-path.bar-name:subject`
     *
     * @param string $component
     * @return string
     */
    public static function componentToView(string $component) : string
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


    /**
     * Split component namespace, name and subject
     *
     * `[$namespace, $component, $subject] = ComponentNaming::splitComponent($component);`
     *
     * @param string $component
     * @return string[]
     */
    public static function splitComponent(string $component) : array
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

    /**
     * Implode component namespace, name and subject
     *
     * `$namespace = ComponentNaming::implodeComponent($namespace, $component, $subject);`
     *
     * @param string|null $namespace
     * @param string      $component
     * @param string|null $subject
     * @return string
     */
    public static function implodeComponent(?string $namespace, string $component, ?string $subject) : string
    {
        return
            (isset($namespace) ? $namespace . '::' : '') .
            $component .
            (isset($subject) ? ':' . $subject : '');
    }

    /**
     * Split component stack and subject
     *
     * `[$stack, $subject] = ComponentNaming::splitStack($component);`
     *
     * @param string $component
     * @return string[]
     */
    public static function splitStack(string $component) : array
    {
        [$namespace, $component, $subject] = static::splitComponent($component);
        return [static::implodeComponent($namespace, $component, null), $subject];
    }

    /**
     * Implode component stack and subject
     *
     *  `$component = ComponentNaming::implodeStack($stack, $subject);`
     *
     * @param string      $stack
     * @param string|null $subject
     * @return string
     */
    public static function implodeStack(string $stack, ?string $subject) : string
    {
        return $stack . (isset($subject) ? ':' . $subject : '');
    }

}