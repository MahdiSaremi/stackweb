<?php

namespace StackWeb\Api\Php;

use ReflectionClass;
use ReflectionMethod;
use StackWeb\Api\StackApi;
use StackWeb\BlankComponent;
use StackWeb\Component;

class StackComponentClass
{

    public function __construct(
        protected StackApi $api,
        protected string $prefixContent,
        protected string $suffixContent,
        protected array $methods,
        // protected array $vars,
    )
    {
    }

    public static function from(StackApi $api, Component $component, string $prefixContent, string $suffixContent)
    {
        $blank = new ReflectionClass(BlankComponent::class);

        $blankMethods = collect($blank->getMethods(ReflectionMethod::IS_PUBLIC))->map->getName()->toArray();
        $methods = collect($blank->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter(fn (ReflectionMethod $method) => !in_array($method->getName(), $blankMethods))
            ->mapWithKeys(function (ReflectionMethod $method)
            {
                return [
                    $method->getName() => $method->getParameters(),
                ];
            })
            ->toArray();

        return new static($api, $prefixContent, $suffixContent, $methods);
    }

    public function addStructure(string $content)
    {
        $this->suffixContent = $content . $this->suffixContent;
    }

    public function build()
    {
        return $this->prefixContent . $this->suffixContent;
    }

}