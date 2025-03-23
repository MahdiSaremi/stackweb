<?php

namespace StackWeb\Compilers\Contracts;

interface Compiler
{
    public function compile();

    public function getOutput(): string;
}