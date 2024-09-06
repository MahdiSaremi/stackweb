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

    public function guessComponentViewName($name)
    {
        return $this->getStackPrefix() . collect(explode('.', $name))->map(Str::kebab($name))->implode('.');
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

    public function newComponent(string $name) : ?ComponentContainer
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

        return null;
    }

    public function invoke(string $name, array $props, array $slots)
    {
        if ($component = $this->newComponent($name))
        {
            $component->mount($props);

            return $component;
        }

        throw new ComponentNotFoundException("Component [$name] not found");
    }

}