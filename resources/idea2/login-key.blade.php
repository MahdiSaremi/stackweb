<?php
$toast = useToast();
$show = useState();
$login = useForm()
    ->inputs([
        'email', 'password',
    ])
    ->action(function(Form $form) use ($toast) {
        [$email, $password] = $form->fetch('email', 'password');
        // Login
        $toast->success("Logged in!");
        return to_route('dashboard');
    });
?>

<div>
    <button @click="{{ $show->js->toggle }}">
        Login
    </button>

    <stack:modal :$show>
        <label>Email:</label>
        <input type="text" x-model="{{ $form->js->email }}">

        <label>Password:</label>
        <input type="password" x-model="{{ $form->js->password }}">

        <button @click="{{ $form->js->submit }}">Login</button>
    </stack:modal>
</div>
