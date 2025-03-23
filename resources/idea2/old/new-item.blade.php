<?php
$parentCreateUser = useParentAction('list', 'createUser');

$form = useForm()
    ->inputs([
        'name' => 'required|string|max:255',
    ])
    ->action(function (Form $form) use ($parentCreateUser) {
        $parentCreateUser($form->validated());
        $form->reset();
    });

?>

<div @stack>
    <input type="text" x-model="{{ $form->js->name }}">
    <button @click="{{ $form->js->submit }}" {{ $form->attr('disabled')->requesting }}>Create</button>
    <button @click="{{ $form->js->submit }}" #disabled="{{ $form->sharp->requesting }}">Create</button>
    <span x-show="{{ $form->js->requesting }}">Loading...</span>
</div>