
component {
    state $count = {{ 0 }}

    render {
        <span>Count: {{ $count }}</span>
        <button onClick="{{ $count++ }}">Add</button>
    }
}

