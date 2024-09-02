
component {
    render {
        <div>
            <Button class="btn-primary" id="12345">
                <Slot icon>@</Slot>
                Hello World
            </Button>

            <AlertButton message="Hello World" />
        </div>
    }
}

component Button (...$attributes) {
    slot $icon

    render {
        <button class="btn" {{ $attributes }}>
            {{ $icon }}
            {{ $slot }}
        </button>
    }
}

component AlertButton ($message) {
    render {
        <Button onClick="{{ $window->alert($message) }}"></Button>
    }
}
