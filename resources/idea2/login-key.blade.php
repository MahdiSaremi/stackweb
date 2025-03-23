<?php
$toast = useToast();
$show = useClientState('show');
$login = useForm('login')
    ->inputs([
        'name', 'password',
    ])
    ->action(function(Form $form) use ($toast) {
        [$name, $password] = $form->fetch('name', 'password');
        // Login
        $toast->success("Logged in!");
        return to_route('dashboard');
    });
?>
<script x-data>
    define({
        openModal() {
            {{ $show->js()->toggle() }}
        }
    })
</script>

<button @click="openModal">Login</button>
<stack:modal :$show>
    Username:
    <input type="text" x-model="{{ $form->js->name }}">
    Password:
    <input type="password" x-model="{{ $form->js->password }}">
    <button @click="{{ $form->js->submit }}">Login</button>
</stack:modal>