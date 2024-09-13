<?php

require __DIR__ . '/vendor/autoload.php';

// $a = [1, 2, 3];
// $x = &$a[1];
// $b = $a;
// $y = &$a[0];
//
// $b[0] = 1000;
// $b[1] = 2000;
// dump($a, $x, $y, $b);

// $a = array_merge([1, 2, 3], [4, 5, 6, 7]);
// $a = [1, 2, 3, ...[3 => 4, 2 => 5]];
$a = [1, 2, 3] + [4, 5, 6];

dd($a);

class x{
    public int $a;
}
class y{
    public string $a;
}
$x = new x;
$y = new y;
$i = '';
$x->a = 1000;
$y->a = &$x->a;
dump($x, $i);
