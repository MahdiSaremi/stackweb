<?php

namespace StackWeb\Api\Js;

use JsPhpize\JsPhpize;
use StackWeb\Api\Php\StackComponentClass;
use StackWeb\Api\StackApi;
use StackWeb\Component;
use StackWeb\Lexer\Js\JsLexer;
use StackWeb\Lexer\Js\JsReader;

class StackJsApi
{

    public function __construct(
        protected StackApi $api,
    )
    {
    }

    public function toAlpineJs(string $js)
    {
        $lexer = new JsLexer(new JsReader($js));

        $result = '';

        foreach ($lexer->tokens as $token)
        {
            if ($result != '') $result .= ',';

            if ($token['type'] == 'var')
            {
                $result .= '"' . addslashes($token['name']) . '":';
                $result .= rtrim(trim($token['value']), ';');
            }
            elseif ($token['type'] == 'function')
            {
                $result .= '"' . addslashes($token['name']) . '":' . ($token['async'] ? 'async ' : '') . 'function';
                $result .= rtrim(trim($token['value']), ';');
            }
        }

        return '{' . $result . '}';
    }

    public function buildPhp(string $js, StackComponentClass $component)
    {
        $lexer = new JsLexer(new JsReader($js));

        $returns = '';

        foreach ($lexer->tokens as $token)
        {
            if ($returns != '') $returns .= ',';

            $returns .= '"' . addslashes($token['name']) . '":' . $token['name'];
        }

        $js .= "\n\nreturn {" . $returns . "}";
        $jsPhpize = new JsPhpize();
        $jsPhp = $jsPhpize->compileCode($js);

        $component->addStructure(<<<PHP
            protected function __runJsData() {
                $jsPhp
            }
        PHP);
    }

}