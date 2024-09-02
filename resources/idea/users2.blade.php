
component {
    state userList

    api run updateUsers () {
        @php
            $state->userList = User::all();
        @endphp
    }

    client test () {
        userList = ['Test']
    }

    render {
        <ul>
            <template x-for="user in userList">
                <User :user="user" />
            </template>
        </ul>

        <button @click={{ $api->updateUsers }}>Refresh</button>
    }
}

component User (User $user) {
    render {
        <li>{{ $user->name }}</li>
    }
}
