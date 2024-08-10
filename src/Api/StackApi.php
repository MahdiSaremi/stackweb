<?php

namespace StackWeb\Api;

use Illuminate\Support\Str;
use StackWeb\Api\Js\StackJsApi;
use StackWeb\Api\Php\StackPhpApi;
use StackWeb\Api\Php\StackPhpFireApi;
use Symfony\Component\CssSelector\Exception\SyntaxErrorException;

class StackApi
{

    public static function isStack(string $source)
    {
        return str_contains($source, 'use StackWeb\Component;');
    }

    protected StackPhpApi $phpApi;
    protected StackPhpFireApi $phpFireApi;
    protected StackJsApi $jsApi;

    public function __construct(
        protected string $name,
        protected string $source,
    )
    {
        $this->phpApi = new StackPhpApi($this);
        $this->phpFireApi = new StackPhpFireApi($this);
        $this->jsApi = new StackJsApi($this);
    }

    public function build() : string
    {
        $source = ltrim($this->source);
        if (!Str::startsWith($source, '<?php'))
        {
            throw new SyntaxErrorException("Stack api [$this->name] must starts with <?php");
        }
        @[$phpSource, $source] = explode('?>', $source, 2);
        if (!$source)
        {
            throw new SyntaxErrorException("Stack api [$this->name] must has a script tag");
        }

        $source = ltrim($source);
        if (!Str::startsWith($source, '<script'))
        {
            throw new SyntaxErrorException("Stack api [$this->name] must has a script tag");
        }
        @[$jsMeta, $source] = explode('>', substr($source, 6), 2);
        if (!$source)
        {
            throw new SyntaxErrorException("Stack api [$this->name] must ends the script tag");
        }
        @[$jsSource, $source] = explode('</script>', $source, 2);
        if (!$source)
        {
            throw new SyntaxErrorException("Stack api [$this->name] must ends the script tag");
        }

        $source = ltrim($source);
        if (!Str::startsWith($source, '<'))
        {
            throw new SyntaxErrorException("Stack api [$this->name] must has a container tag");
        }
        @[$htmlPrefix, $source] = explode('>', $source, 2);
        if (!$source)
        {
            throw new SyntaxErrorException("Stack api [$this->name] must ends the container tag");
        }

        $component = $this->phpApi->toClass($phpSource);
        $jsSource = $this->phpFireApi->buildJs($jsSource, $component);
        $jsAlpine = $this->jsApi->toAlpineJs($jsSource);
        $this->jsApi->buildPhp($jsSource, $component);

        $htmlSource = $htmlPrefix . " x-data=\"" . e($jsAlpine) . "\">" . $source;
        $escapedHtml = "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], $htmlSource) . "'";

        $component->addStructure(
            <<<PHP
            protected function renderBlade()
            {
                return {$escapedHtml};
            }
        PHP
        );

        return $component->build();
    }

    public static function precompiler($content)
    {
        $api = new StackApi();
    }

}