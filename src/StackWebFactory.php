<?php

namespace StackWeb;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use StackWeb\Exceptions\ComponentNotFoundException;
use StackWeb\Foundation\Component;
use StackWeb\Foundation\ComponentContainer;
use StackWeb\Foundation\Stack;
use StackWeb\Renderer\JsRenderer;

class StackWebFactory
{

    public function __construct()
    {
        $this->stackCache = storage_path('framework/views');
    }


    protected string $stackCache;

    public function setStackCache(string $path)
    {
        $this->stackCache = $path;
    }

    public function getStackCache()
    {
        return $this->stackCache;
    }

    public function getStackCachedComponentPath($relativePath)
    {
        return $this->getStackCache() . '/' . hash('xxh128', 'sw' . $relativePath) . '.stacked.php';
    }


    protected string $pathPrefix = 'stack.';

    public function setStackPrefix(string $path)
    {
        $this->pathPrefix = $path;
    }

    public function getStackPrefix()
    {
        return $this->pathPrefix;
    }

    public function guessComponentViewName(string $component)
    {
        [$stack, ] = ComponentNaming::splitStack($component);

        return $this->guessStackViewName($stack);
    }

    public function guessStackViewName(string $stack)
    {
        return ComponentNaming::componentToView($stack);
    }




    /**
     * @var array<string, Stack>
     */
    protected array $loadedStacks = [];

    public function stackLoaded(string $stack) : bool
    {
        return array_key_exists($stack, $this->loadedStacks);
    }

    public function stackExists(string $stack) : bool
    {
        return (bool) $this->importStack($stack);
    }

    public function importStack(string $stack) : ?Stack
    {
        if ($this->stackLoaded($stack))
        {
            return $this->loadedStacks[$stack];
        }

        if (View::exists($view = $this->guessComponentViewName($stack)))
        {
            $this->importingName = $stack;
            try
            {
                view($view, [
                    'stack' => $stack,
                ])->render();

                if (!$this->stackLoaded($stack))
                {
                    return $this->loadedStacks[$stack] = null;
                }
            }
            finally
            {
                unset($this->importingName);
            }
        }
        else
        {
            return $this->loadedStacks[$stack] = null;
        }

        return $this->loadedStacks[$stack] ?? null;
    }

    public function componentExists(string $component) : bool
    {
        [$stack, $subject] = ComponentNaming::splitStack($component);

        return (bool) $this->importStack($stack)?->has($subject);
    }

    public function importComponent(string $component) : ?Component
    {
        [$stack, $subject] = ComponentNaming::splitStack($component);

        return $this->importStack($stack)?->get($subject);
    }


    protected string $importingName;

    public function export(Stack $stack, ?string $component = null)
    {
        $this->loadedStacks[$component ?? $this->importingName] = $stack;
    }


    public function newComponent(string $component) : ComponentContainer
    {
        [$stack, $subject] = ComponentNaming::splitStack($component);

        if ($stackObject = $this->importStack($stack))
        {
            if ($stackObject->has($subject))
            {
                return $stackObject->create($subject);
            }

            throw new ComponentNotFoundException("Component [$component] not found");
        }

        throw new ComponentNotFoundException("Component [$stack] not found");
    }

    public function invoke(string $name, array $props, array $slots) : ComponentContainer
    {
        $component = $this->newComponent($name);

        $component->mount($props);

        return $component;
    }


    /**
     * @param Component   $component
     * @param Component[] $deps
     * @return void
     */
    protected function extractRecursiveComponentDeps(Component $component, array &$deps)
    {
        if (in_array($component, $deps))
        {
            return;
        }

        $deps[] = $component;

        foreach ($component->depComponents as $depName)
        {
            if ($depComponent = $this->importComponent($depName))
            {
                $this->extractRecursiveComponentDeps($depComponent, $deps);
            }
        }
    }


    public function responsePage(string $component)
    {
        $componentName = $component;
        $component = $this->invoke($componentName, [], []);

        $app = null;
        if ($component->component->renderApi)
        {
            $app = $component->component->renderApi->call($component);
        }

        /** @var Component[] $depComponents */
        $depComponents = [];
        $this->extractRecursiveComponentDeps($component->component, $depComponents);

        $js = "window.StackWebComponents = {";
        foreach ($depComponents as $dep)
        {
            if ($dep->renderCli)
            {
                $js .= "[" . JsRenderer::render($dep->name) . "]: () => ";
                $js .= $dep->renderCli->call($dep->getStatic());
                $js .= ",";
            }
        }
        $js .= "}";

        return response(sprintf(
            <<<'HTML'
            <!doctype html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Document</title>
                %s
            </head>
            <body>
                <div id="app">%s</div>
                
                <script>%s</script>
                <script>
                    document.getElementById("app").innerHTML = "" // todo: remove to see the ssr
                    window.root = new StackWeb.Root(new StackWeb.Group([
                        new StackWeb.Invoke(window.StackWebComponents["%s"](), {}, {})
                    ]))
                    window.root.mount(null, null, document.getElementById("app"))
                </script>
            </body>
            </html>
            HTML,
            '<script src="'.e(route('stackweb.js')).'"></script>',
            $app,
            $js,
            e($componentName),
        ));
    }

}