<?php

use JsPhpize\Compiler\Compiler;
use JsPhpize\JsPhpize;
use JsPhpize\Parser\Parser;
use StackWeb\Lexer\Js\JsLexer;
use StackWeb\Lexer\Js\JsReader;

require __DIR__ . '/vendor/autoload.php';


$reader = new JsReader(<<<JS
    let x = function () {
        return 20;
    };

    let y = 20
        .toLower()
        
    let z = 30
    
    async function send() {
        
    }
JS);

$js = new JsLexer($reader);

dd($js);

// $js = new JsPhpize();
//
// $parser = new Parser($js, "let x = 20", null);
// $block = $parser->parse();
//
// dd($block);
