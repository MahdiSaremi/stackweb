<?php

namespace StackWeb\Engine;

use Illuminate\Contracts\View\Engine;

class StackWebEngine implements Engine
{

    public function get($path, array $data = [])
    {
        return $path;
    }

}