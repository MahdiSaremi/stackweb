<?php

namespace StackWeb\Compilers\Stack\Structs;

use StackWeb\Compilers\Concerns\TokenTrait;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\Stack\Tokens\_ImportToken;
use StackWeb\Compilers\StringReader;

class _StackStruct implements Token
{
    use TokenTrait;

    public function __construct(
        public StringReader $reader,
        public int $startOffset,
        public int $endOffset,

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

    public function resolveComponent(string $name)
    {
        $dotCount = substr_count($name, '.');
        if ($dotCount === 0)
        {
            return $this->imports[$name] ?? null;
        }
        elseif ($dotCount === 1)
        {
            [$name, $subject] = explode('.', $name);
            if (isset($this->imports[$name]))
            {
                return $this->imports[$name] . ':';
            }
        }
    }

}