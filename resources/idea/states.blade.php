
component {
    render {
        <Counter />
        <TodoList />
    }
}

component Counter {
    state $count = 0

    render {
        <span>{{ $count }}</span>
        <button onClick="{{ $count++ }}">Add</button>
    }
}

component TodoList {
    state $todos = []
    state $text = ''

    render {
        <input type="text" onChange="{{ $text = $event->value }}" value="{{ $text }}">
        <button onClick="{{ $todos[] = $text }}"></button>

        @foreach($todos as $todo)
            <p>{{ $todo }}</p>
        @endforeach
    }
}
