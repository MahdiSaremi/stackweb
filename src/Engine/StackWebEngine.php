<?php

namespace StackWeb\Engine;

use Illuminate\Contracts\View\Engine;
use Illuminate\Support\Str;
use StackWeb\Compilers\Stack\StackCompiler;
use StackWeb\Compilers\Stack\StackParser;
use StackWeb\Compilers\Stack\Structs\_StackStruct;
use StackWeb\Compilers\StringReader;
use StackWeb\StackWeb;

class StackWebEngine implements Engine
{
    public function get($path, array $data = [])
    {
        $relativePath = Str::after($path, base_path());
        $cached = StackWeb::getStackCachedComponentPath($relativePath);

        // if (!file_exists($cached) || filemtime($path) >= filemtime($cached))
        if (true) { // todo test
            $string = new StringReader(file_get_contents($path), $path);

            $parser = StackCompiler::from($string);
            $parser->compile();

            file_put_contents($cached, $parser->getOutput());
        }

        $this->include($cached);
    }

    public function include($__path)
    {
        return include $__path;
    }
}
