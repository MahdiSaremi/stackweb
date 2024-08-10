<script>

    async function getUsers()
    {
        return await server.fire(<?php function ()
        {
            return User::all();
        } ?>)()
    }

    let users = server.state(getUsers)

    async function refreshList()
    {
        users = await getUsers()
    }

</script>

<div>
    <stack::template for="user in users">
        <stack::list-item item="user" />
    </stack::template>

    <stack::new-item />
</div>