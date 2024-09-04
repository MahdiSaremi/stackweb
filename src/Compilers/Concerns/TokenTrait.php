<?php

namespace StackWeb\Compilers\Concerns;

use StackWeb\Compilers\StringReader;

trait TokenTrait
{

    public function getReader() : StringReader
    {
        return $this->reader;
    }

    public function getStartOffset() : int
    {
        return $this->startOffset;
    }

    public function getEndOffset() : int
    {
        return $this->endOffset;
    }

    public function getInfo() : array
    {
        return [$this->getReader(), $this->getStartOffset(), $this->getEndOffset()];
    }

    public function syntaxError(string $message)
    {
        $this->getReader()->syntaxErrorOn($this, $message);
    }

}