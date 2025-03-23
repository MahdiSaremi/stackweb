<?php
$users = useServerState();

$refreshUsers = useAction(function () use ($users) {
    $users->set(
        User::all(),
    );
})->mount();

$deleteUser = useServerAction(function (User $user) use ($refreshUsers) {
    $user->delete();
    $refreshUsers();
});

$createUser = useServerAction(function (array $attributes) use ($refreshUsers) {
    User::create($attributes);
    $refreshUsers();
});
?>

<div>
    <stack:foreach {{ $users->loopAs($user) }}>
        <stack:list-item :$user />
    </stack:foreach>

    <stack::new-item />
</div>