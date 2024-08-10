<script>

    let item = prop()

    async function deleteUser()
    {
        if (await server.fire(<?php function ($id)
        {
            $user = User::findOrFail($id);

            return $user->delete();
        } ?>)(user.id))
        {
            refrestList()
        }
    }

</script>

<div @stack>
    <span>User : {item.name}</span>
    <button @click="deleteUser">Delete</button>
</div>