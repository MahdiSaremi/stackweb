
component {
    state $todos = [];

    render {
        <ShowTodos todos={{ $todos }} />

        <ShowTools />
    }
}

component ShowTodos ($todos) {
    render {
        <ul>
            @foreach ($todos as $todo)
                <Todo todo={{ $todo }} />
            @endforeach
        </ul>
    }
}

component Todo ($todo) {
    render {
        <li>
            {{ $todo }}
            -
            <button>Edit</button>
            <button>Delete</button>
        </li>
    }
}

component ShowTools {
    state $showAdd = false;
    state $text = '';

    client add {
        @php
            $parent->todos[] = $text;
            $state->text = '';
            $state->showAdd = false;
        @endphp
    }

    render {
        @if(!$state->showAdd)
            <button onClick={{ fn() => $state->showAdd = true }}>Add</button>
        @else
            <button onClick={{ fn() => $state->showAdd = false }}>Hide Add</button>
            <input type="text" value={{ $state->text }} onChange={{ fn ($event) => $state->text = $event->text }}>
            <span>Add {{ $state->text }} ? </span>
            <button onClick={{ $client->add }}>Yes, Add It</button>
        @endif
    }
}
