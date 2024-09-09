<?php

namespace StackWeb\Compilers\Stack\Structs;

use Illuminate\Support\Str;
use StackWeb\Compilers\Concerns\TokenTrait;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\Stack\Tokens\_ImportToken;
use StackWeb\Compilers\StringReader;
use StackWeb\ComponentNaming;

class _StackStruct implements Token
{
    use TokenTrait;

    public function __construct(
        public StringReader $reader,
        public int $startOffset,
        public int $endOffset,

        public string $name,

        /** @var string[] */
        public array $componentNames,
        /** @var array<string, _ComponentStruct> */
        public array $components,
    )
    {
    }

    /**
     * @var array<string, string>
     */
    public array $imports = [];

    public function import(_ImportToken $import)
    {
        $this->imports[$import->aliasAs] = $import->componentName;
    }

    public function resolveAliasComponent(string $name)
    {
        [$namespace, $component, $subject] = ComponentNaming::splitComponent($name);

        // Contains namespace
        if (isset($namespace))
        {
            if ($namespace === '')
            {
                return ComponentNaming::implodeComponent(null, $component, $subject);
            }

            return null;
        }

        if (is_null($subject) && in_array($component, $this->componentNames))
        {
            return $this->name . ':' . $component;
        }

        if (str_contains($component, '.'))
        {
            $base = Str::before($component, '.');
            $component = Str::after($component, '.');
        }
        else
        {
            $base = $component;
            $component = null;
        }

        if (isset($this->imports[$base]))
        {
            $base = $this->imports[$base];

            return ComponentNaming::implodeComponent(
                null,
                $base . (isset($component) ? '.' . $component : ''),
                $subject
            );
        }

        return null;
    }

}