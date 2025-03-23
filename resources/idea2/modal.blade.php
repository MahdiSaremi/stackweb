<?php
$slot = useSlot();
$show = useModel('show');
?>

<div class="fixed inset-0 bg-black/50" x-show="{{ $show->js }}" @click="{{ $show->js->set(false) }}">
    <div class="bg-white rounded-lg p-6" @click.prevent.stop="">
        {{ $slot }}
    </div>
</div>