<?php

namespace StackWeb\Compilers\HtmlX\Structs;

interface _Node extends _Item
{

    /**
     * @return _Node[]
     */
    public function getChildren() : array;

    /**
     * @return _Node|null
     */
    public function getParent() : ?_Node;

}