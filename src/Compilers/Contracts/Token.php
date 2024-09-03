<?php

namespace StackWeb\Compilers\Contracts;

use StackWeb\Compilers\StringReader;

interface Token
{

    public function getReader() : StringReader;

    public function getStartOffset() : int;

    public function getEndOffset() : int;

}