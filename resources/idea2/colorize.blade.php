<?php
$slot = useSlot();
$colorProp = useProp('color')->string()->default('blue');
$color = useState('color')->default($colorProp);

useTailwindPattern('text-{color}-{adjust}')->with([
    'color' => ['blue', 'red', 'green', 'white'],
    'adjust' => [100, 200, 300, 400, 500, 600, 700, 800, 900],
]);
?>

<button @click="{{ $color->js->set('red') }}">Red</button>
<button @click="{{ $color->js->set('blue') }}">Blue</button>
<span #class="text-{{ $color->sharp }}-500">
    {{ $slot->withClass('text-gray-700') }}
</span>