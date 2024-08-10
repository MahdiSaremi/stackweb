<?php

namespace StackWeb\Api\Php;

use StackWeb\Api\StackApi;
use StackWeb\Api\StackSyntaxError;
use StackWeb\Component;

class StackPhpApi
{

    public function __construct(
        protected StackApi $api,
    )
    {
    }

    public function toClass(string $php)
    {
        $temp = tempnam(sys_get_temp_dir(), 'stack_web_');
        try
        {
            file_put_contents($temp, $php);
            $class = include $temp;

            if ($class instanceof Component)
            {
                if (preg_match('/^([\s\S]*)(return\s+new\s+class\s*(\(.*\))?\s+extends\s+(.*?)[\s\n]*\{)([\s\S]*)$/', $php, $matches))
                {
                    $prefix = $matches[1] . $matches[2];
                    $suffix = $matches[5];

                    return StackComponentClass::from(
                        $this->api,
                        $class,
                        $prefix,
                        $suffix,
                    );
                }
                else
                {
                    throw new StackSyntaxError("Php should has line [return new class extends Component]");
                }
            }
            else
            {
                throw new StackSyntaxError("Php should return a component");
            }
        }
        finally
        {
            @unlink($temp);
        }
    }

}