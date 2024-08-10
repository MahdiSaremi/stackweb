<?php

namespace StackWeb;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\View\Compilers\BladeCompiler;

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

    public function getRelativePath($viewPath)
    {
        $root = base_path();
        return str_starts_with($viewPath, $root) ? substr($viewPath, strlen($root)) : $viewPath;
    }

    public function getStackCachedComponentPath($relativePath)
    {
        return $this->getStackCache() . '/' . hash('xxh128', 'sw' . $relativePath) . '.stack.php';
    }


    // public function componentByView(string $view)
    // {
    //     if (View::exists($view))
    //     {
    //         $view = view($view);
    //
    //         return $this->componentByRelativePath(
    //             $this->getRelativePath($view->getPath())
    //         );
    //
    //         // TODO: Re-render view when modified
    //     }
    // }

    public function componentByRelativePath(string $relativePath) : Component
    {
        $path = $this->getStackCachedComponentPath($relativePath);

        if (!file_exists($path))
        {
            throw new Exceptions\ComponentNotFound("Component [$relativePath] not found");
        }

        $result = include $path;

        if (!($result instanceof Component))
        {
            throw new \TypeError("Component [$relativePath] expected to return a component, run 'php artisan view:clear'");
        }

        return $result;
    }


    /**
     * @var Component[]
     */
    protected array $components;

    public function push(Component $component) : void
    {
        $this->components[] = $component;
    }

    public function pop() : Component
    {
        return array_pop($this->components);
    }

    public function peek() : Component
    {
        return end($this->components);
    }

    public function parent() : ?Component
    {
        return $this->components[count($this->components) - 1] ?? null;
    }

    public function parentOf(Component $component) : ?Component
    {
        $search = array_search($component, $this->components);

        return $search ? $this->components[$search - 1] : null;
    }

}