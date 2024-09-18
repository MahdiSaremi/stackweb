<?php

namespace StackWeb\Compilers\CliPhp\Concerns;
use PhpParser\Node\Expr;

trait CompilesOperators
{

    public function binaryOpPlus(Expr\BinaryOp\Plus $expr)
    {
        $this->js->append("PHPUtils.opAdd(");
        $this->expr($expr->left);
        $this->js->append(", ");
        $this->expr($expr->right);
        $this->js->append(")");
    }

}