<script>
    const user = server.state({{ $user->only('id', 'name') }});
</script>

<div>
    {user.name}
</div>
