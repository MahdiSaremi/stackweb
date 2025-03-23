<?php
$user = useState('user')->type(User::class)->safe();

$parentDeleteUser = useParentAction('list', 'deleteUser');
$deleteUser = useAction(function () use ($parentDeleteUser, $user) {
    $parentDeleteUser($user);
});
?>

<div @stack>
    <span>User : {{ $user->value->name  }}</span>
    <button @click="{{ $deleteUser->js }}">Delete</button>
</div>