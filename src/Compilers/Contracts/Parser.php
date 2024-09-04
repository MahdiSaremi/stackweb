<?php

namespace StackWeb\Compilers\Contracts;

interface Parser
{

    public function parse() : void;

    public function getStruct() : Token;

}