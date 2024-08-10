<?php

namespace StackWeb\Tests;

use Illuminate\Support\Facades\Blade;
use StackWeb\Api\Js\StackJsApi;
use StackWeb\Api\Php\StackPhpApi;
use StackWeb\Api\StackApi;

class AppTest extends TestCase
{

    public function test_js()
    {
        dd(StackJsApi::convert(<<<JS
            let x = 20;

            let increase = () => x++;
            
            function decrease()
            {
                x--;
            }
        JS));
    }

    public function test_php()
    {
        dd(StackPhpApi::convert(<<<'PHP'
            use StackWeb\Component;
            
            return new class extends Component
            {
                public function add($name)
                {
                    return true;
                }
            }
        PHP));
    }

    public function test_main()
    {
        $api = new StackApi('test', "<?php\n". <<<'PHP'
            use StackWeb\Component;
            
            return new class extends Component
            {
                public function submit(int $x)
                {
                    // Do something
                }
            };
            
            ?>
            
            <script>
                let x = 20;
                
                let submit = () => component.submit(x)
            </script>

            <div>
                Hello {{ $user->name }}
            
                <button @click='x++'>+</button>
                <js::text value='x' />
                <button @click='x--'>-</button>
                
                <button @click='submit'>Submit</button>
            </div>
        PHP
        );

        dd($api->build());
    }

    public function test_22()
    {
        dd(Blade::render("<?php\n". <<<'PHP'
            use StackWeb\Component;
            
            return new class extends Component
            {
            };
            
            ?>
            
            <script>
                let getName = () => {
                    return "Nike Shoe"
                };
            
                let x = 2024
                let product_name = getName()
                let some_html = "<b>Title</b>"
            </script>

            <div>
                <x-js::text tag='h1' value='product_name' />
            
                <button @click='x++'>Add</button>
                <x-js::text value='x' />
                <button @click='x--'>Sub</button>
                
                <x-js::text value='some_html' />
                <x-js::html value='some_html' />
            </div>
        PHP));
    }

}