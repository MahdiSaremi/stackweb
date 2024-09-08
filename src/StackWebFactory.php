<?php

namespace StackWeb;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use StackWeb\Exceptions\ComponentNotFoundException;
use StackWeb\Foundation\ComponentContainer;
use StackWeb\Foundation\Stack;

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
        [$namespace, $component] = ComponentNaming::splitComponent($component);
        $component = ComponentNaming::implodeComponent($namespace, $this->getStackPrefix() . $component, null);

        return ComponentNaming::componentToView($component);
    }




    /**
     * @var array<string, Stack>
     */
    protected array $loadedStacks = [];

    protected function tryGetStack(string $name) : ?Stack
    {
        if (array_key_exists($name, $this->loadedStacks))
        {
            return $this->loadedStacks[$name];
        }

        if (View::exists($view = $this->guessComponentViewName($name)))
        {
            $this->importingName = $name;
            try
            {
                view($view)->render();

                if (!array_key_exists($name, $this->loadedStacks))
                {
                    return $this->loadedStacks[$name] = null;
                }
            }
            finally
            {
                unset($this->importingName);
            }
        }

        return $this->loadedStacks[$name] ?? null;
    }

    protected string $importingName;

    public function export(Stack $stack)
    {
        $this->loadedStacks[$this->importingName] = $stack;
    }

    public function newComponent(string $name) : ComponentContainer
    {
        if ($stack = $this->tryGetStack($name))
        {
            if ($stack->has(''))
            {
                return $stack->create('');
            }
        }

        if (str_contains($name, '.') && $stack = $this->tryGetStack(Str::beforeLast($name, '.')))
        {
            if ($stack->has($com = Str::afterLast($name, '.')))
            {
                return $stack->create($com);
            }
        }

        throw new ComponentNotFoundException("Component [$name] not found");
    }

    public function invoke(string $name, array $props, array $slots)
    {
        $component = $this->newComponent($name);

        $component->mount($props);

        return $component;
    }

    public function responsePage(string $component)
    {
        $component = $this->invoke($component, [], []);

        $content = '';

        if ($component->component->renderApi)
        {
            $content .= $component->component->renderApi->call($component);
        }

        if ($component->component->renderCli)
        {
            $content .= $component->component->renderCli->call($component);
        }

        return response($content);
    }

}