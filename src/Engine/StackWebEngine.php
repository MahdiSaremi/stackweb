<?php

namespace StackWeb\Engine;

use Illuminate\Contracts\View\Engine;
use Illuminate\Support\Str;
use StackWeb\Compilers\Stack\StackParser;
use StackWeb\Compilers\Stack\Structs\_StackStruct;
use StackWeb\Compilers\StringReader;
use StackWeb\Renderer\Builder\SourceBuilder;
use StackWeb\Renderer\SourceRendererDev;
use StackWeb\StackWeb;

class StackWebEngine implements Engine
{

    public function get($path, array $data = [])
    {
        $relativePath = Str::after($path, base_path());
        $cached = StackWeb::getStackCachedComponentPath($relativePath);

        if (!file_exists($cached) || filemtime($path) >= filemtime($cached))
        {
            $string = new StringReader(file_get_contents($path), $path);

            $parser = StackParser::from($string);
            $parser->parse();

            /** @var _StackStruct $stack */
            $stack = $parser->getStruct();

            $out = new SourceBuilder;
            $renderer = new SourceRendererDev($string);
            $renderer->renderStack($out, $stack);

            file_put_contents($cached, $out->toCode());
        }

        $this->include($cached);
    }

    public function include($__path)
    {
        include $__path;
    }

}
