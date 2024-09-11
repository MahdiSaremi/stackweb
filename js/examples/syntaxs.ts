import {PHPUtils, Scope} from "../php";
import {PHPArray} from "../php-types";

let local = new Scope(), v = local.v


/**
 * Assignments & Accessors
 */

// $a = 1
v.a = 1
// $a = $b
v.a = v.b
// $a['x'] = $b['y']
    ...??? WTF
PHPUtils.setOffset(v.a, 'x', PHPUtils.getOffset(v.b, 'y'))
// $a['x']['y'] = $b
PHPUtils.setOffset(PHPUtils.getOffset(a.x, 'x'), 'y', v.b)
// $a[] = $b
PHPUtils.pushOffset(v.a, v.b)



/**
 * Arrays
 */

// [1, 2, 3]
PHPArray.from([1, 2, 3])
// ['a' => 'A']
PHPArray.from({a: 'A'})
// [1, 'a' => 'A']
PHPArray.from({0: 1, a: 'A'})
// [1, ...$b, 2]
PHPArray.from([1]).merge(v.b).merge([2])


/**
 * References
 */

// $a = &$b
v.a = local.ref('b')
// $a = &$b['x']
v.a = PHPUtils.getOffsetRef(v.b, 'x')
// $a = &$b->x
v.a = PHPUtils.getPropRef(v.b, 'x')
// $a = &$b['x']->y
v.a = PHPUtils.getPropRef(PHPUtils.getProp(v.b, 'x'), 'y')

