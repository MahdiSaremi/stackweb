
component {
    state $userList

    api run updateUsers () = {
        @php
            $state->userList = User::all();
        @endphp
    }

    render {
        <ul>
            @foreach($userList as $user)
                <User user={{ $user }} />
            @endforeach
        </ul>

        <button @click={{ $api->updateUsers }}>Refresh</button>
    }
}

component User (User $user) {
    render {
        <li>{{ $user->name }}</li>
    }
}
