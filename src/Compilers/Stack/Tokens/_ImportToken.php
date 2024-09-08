<?php

namespace StackWeb\Compilers\Stack\Tokens;

use Illuminate\Support\Str;
use StackWeb\Compilers\Concerns\TokenTrait;
use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\StringReader;

readonly class _ImportToken implements Token
{
    use TokenTrait;

    public string $componentName;

    public string $aliasAs;

    public function __construct(
        public StringReader $reader,
        public int $startOffset,
        public int $endOffset,

        public string $name,
        public ?string $as = null,
        public ?string $subject = null,
    )
    {
        $this->componentName = $this->name . (isset($this->subject) ? ':' . $this->subject : '');
        $this->aliasAs = $this->as ?? $this->subject ?? Str::afterLast($this->name, '.');
    }

}