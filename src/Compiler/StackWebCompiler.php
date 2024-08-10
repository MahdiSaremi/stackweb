<?php

namespace StackWeb\Compiler;

use Illuminate\View\Compilers\BladeCompiler;
use StackWeb\Api\StackApi;
use StackWeb\StackWeb;

class StackWebCompiler
{

    protected string $path;
    protected string $relativePath;
    protected string $componentPath;

    public function render(string $source)
    {
        if (StackApi::isStack($source))
        {
            /** @var BladeCompiler $compiler */
            $compiler = app('blade.compiler');

            $this->path = $compiler->getPath();
            $this->relativePath = StackWeb::getRelativePath($this->path);
            $this->componentPath = StackWeb::getStackCachedComponentPath($this->relativePath);

            $api = new StackApi($this->path, $source);

            @mkdir(dirname($this->componentPath), recursive: true);
            file_put_contents($this->componentPath, $api->build());

            $escapedRelativePath = "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], $this->relativePath) . "'";

            return trim(<<<PHP
            <?php
                echo \StackWeb\StackWeb::componentByRelativePath({$escapedRelativePath})->render();
            ?>
            PHP);
        }

        return $source;
    }

}