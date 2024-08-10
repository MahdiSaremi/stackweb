<?php

use StackWeb\Component;

return new class extends Component
{
    public function addBackend($name)
    {
        User::add(
            [
                'name' => $name,
            ]
        );

        return true;
    }
}

?>

<script>

    let name = state('');

    async function add() {
        await api.addBackend(name)
        name = '';
        refreshList()
    }

    let u = '\''

</script>

<div @stack>
    <input type="text" x-model="name">
    <button @click="add">Add</button>
    <span x-loading>Loading...</span>
</div>