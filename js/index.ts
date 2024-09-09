export abstract class Entity {

    parent: Entity
    parentE: Entity
    el: Element
    isMounted = false

    protected abstract onMount(): void

    protected abstract onUnmount(): void

    protected abstract onMorph(other: Entity): void

    public mount(parent: Entity, parentE: Entity, el: Element) {
        this.parent = parent
        this.parentE = parentE
        this.el = el
        this.isMounted = false
        this.onMount()
        this.isMounted = true
    }

    public unmount() {
        this.onUnmount()
        this.isMounted = false
    }

    public morph(other: Entity) {
        if (this.isStatic) return

        this.onMorph(other)
    }


    isStatic: boolean = false
    public static() {
        this.isStatic = true
        return this
    }


    public abstract resolveRelativeNodes(): [Node, Node]

    public abstract resolveNextNodeOf(child: Entity): Node


    protected insertNode(node: Node) {
        if (this.parentE.isMounted) {
            let nextOfMe = this.parent.resolveNextNodeOf(this)
            if (nextOfMe) {
                this.parent.el.insertBefore(node, nextOfMe)
            } else {
                this.parent.el.appendChild(node)
            }
        } else {
            this.parent.el.appendChild(node)
        }
    }

}

export class Root extends Entity {
    source: Group

    constructor(source: Group) {
        super();
        this.source = source
    }

    protected onMount() {
        this.source.mount(this, this, this.el)
    }

    protected onUnmount() {
        this.source.unmount()
    }

    onMorph(other: Entity) {
        this.source.morph((other as Root).source)
    }

    resolveRelativeNodes(): [Node, Node] {
        return this.source.resolveRelativeNodes()
    }

    resolveNextNodeOf(child: Entity): Node {
        return undefined
    }
}

export class Group extends Entity {
    source: Array<Entity>

    constructor(source: Array<Entity>) {
        super()
        this.source = source
    }

    onMount() {
        for (const i in this.source) {
            this.source[i].mount(this, this.parentE, this.el)
        }
    }

    onUnmount() {
        for (const i in this.source) {
            this.source[i].unmount()
        }
    }

    onMorph(other: Entity) {
        for (const i in this.source) {
            this.source[i].morph((other as Group).source[i])
        }
    }

    resolveRelativeNodes(): [Node, Node] {
        if (this.source.length == 0) {
            return [undefined, undefined]
        } else if (this.source.length == 1) {
            return this.source[0].resolveRelativeNodes()
        } else {
            let a, b, i

            // Todo : Should save in array to better performance
            for (i = 0; i < this.source.length; i++) {
                let cur = this.source[i].resolveRelativeNodes()
                if (cur[0] || cur[1]) {
                    a = cur[0] ?? cur[1]
                    break
                }
            }
            for (i = this.source.length - 1; i >= 0; i--) {
                let cur = this.source[i].resolveRelativeNodes()
                if (cur[0] || cur[1]) {
                    b = cur[1] ?? cur[0]
                    break
                }
            }

            return [a, b]
        }
    }

    resolveNextNodeOf(child: Entity): Node {
        let index = this.source.indexOf(child)
        for (let i = index + 1; i < this.source.length; i++) {
            let cur = this.source[i].resolveRelativeNodes()
            if (cur[0] || cur[1]) {
                return cur[0] ?? cur[1]
            }
        }

        if (this.parent) {
            return this.parent.resolveNextNodeOf(this)
        }

        return undefined
    }
}

export interface DomRegister {
    name: string
    attrs: Object
    slot: Group
}

export class Dom extends Entity {
    source: DomRegister

    constructor(source: DomRegister) {
        super()
        this.source = source
    }

    onMount() {
        this.el = document.createElement(this.source.name)

        this.insertNode(this.el)

        this.source.slot.mount(this, this, this.el)
    }

    onUnmount() {
        this.el.remove()
        this.el = undefined
    }

    onMorph(other: Entity) {
        this.source.slot.morph((other as Dom).source.slot)
    }

    resolveRelativeNodes(): [Node, Node] {
        return [this.el, this.el];
    }

    resolveNextNodeOf(child: Entity): Node {
        return undefined;
    }
}

export class Text extends Entity {
    node: Node
    value: string

    constructor(value: string) {
        super();
        this.value = value
    }

    onMount() {
        this.node = document.createTextNode(this.value)
        this.insertNode(this.node)
    }

    onUnmount() {
        this.el.removeChild(this.node)
        this.node = undefined
    }

    onMorph(other: Entity) {
        this.value = (other as Text).value
        this.node.textContent = this.value
    }

    resolveRelativeNodes(): [Node, Node] {
        return [this.node, this.node];
    }

    resolveNextNodeOf(child: Entity): Node {
        return undefined;
    }
}

export class HelloWorld extends Entity {
    node: Node

    onMount() {
        this.node = document.createTextNode('Hello World (' + Math.floor(Math.random() * 100) + ')')
        this.insertNode(this.node)
    }

    onUnmount() {
        this.el.removeChild(this.node)
        this.node = undefined
    }

    onMorph(other: Entity) {
        this.node.textContent = 'Hello World (' + Math.floor(Math.random() * 100) + ')'
    }

    resolveRelativeNodes(): [Node, Node] {
        return [this.node, this.node];
    }

    resolveNextNodeOf(child: Entity): Node {
        return undefined;
    }
}

export interface ComponentRegister {
    states: ($: Invoke) => Object
    slots: ComponentRegisterSlots,
    render: ($: Invoke) => Group,
}

export interface ComponentRegisterSlots {
    [index: string]: ($: Invoke) => Group,
}

export class Component {
    source: ComponentRegister

    constructor(source: ComponentRegister) {
        this.source = source
    }

    // Modes: 0 - When Update, 1 - Always Morph, 2 - Never Morph
    morphMode: number = 0

    morphType(mode: number) {
        this.morphMode = mode
        return this
    }
}

export class Invoke extends Entity {
    component: Component
    slots: Object
    props: Object
    states = {}
    content: Group

    constructor(component: Component, slots: Object, attrs: Object) {
        super()
        this.component = component
        this.slots = slots
        this.props = attrs
    }

    onMount() {
        for (const slotKey in this.component.source.slots) {
            if (this.slots[slotKey] === undefined) {
                this.slots[slotKey] = this.component.source.slots[slotKey](this)
            }
        }

        this.states = this.component.source.states(this)
        this.content = this.component.source.render(this)

        this.content.mount(this, this.parentE, this.el)
    }

    onUnmount() {
        this.content.unmount()
    }

    reset() {
        this.states = this.component.source.states(this)
    }

    changed: boolean = false

    refresh() {
        const newRender = this.component.source.render(this)

        this.content.morph(newRender)

        this.changed = false
    }

    onMorph(other: Entity) {
        if (this.component.morphMode == 0) {
            let changed = false
            if (JSON.stringify(this.slots) !== JSON.stringify((other as Invoke).slots)) {
                changed = true
                this.slots = (other as Invoke).slots
            }
            if (JSON.stringify(this.props) !== JSON.stringify((other as Invoke).props)) {
                changed = true
                this.props = (other as Invoke).props
            }

            if (changed) {
                this.content.morph((other as Invoke).component.source.render(this))
            }
        }
        if (this.component.morphMode == 1) {
            this.content.morph((other as Invoke).component.source.render(this))
        }
    }

    getState(name: string) {
        return this.states[name]
    }

    setState(name: string, value: any) {
        this.states[name] = value
        this.changed = true
    }

    track(callback: () => any) {
        callback()
        if (this.changed) {
            this.refresh()
        }
    }

    getSlot(name: string = '') {
        return this.slots[name]
    }

    getProp(name: string) {
        return this.props[name]
    }

    get(name: string) {
        if (this.states[name] !== undefined) {
            return this.states[name]
        }
        if (this.props[name] !== undefined) {
            return this.props[name]
        }
        if (this.slots[name] !== undefined) {
            return this.slots[name]
        }

        return undefined
    }

    resolveRelativeNodes(): [Node, Node] {
        return this.content.resolveRelativeNodes()
    }

    resolveNextNodeOf(child: Entity): Node {
        return this.parent.resolveNextNodeOf(this)
    }
}

export class If extends Entity {
    source: Group
    condition: boolean

    constructor(condition: boolean, source: Group) {
        super()
        this.source = source
        this.condition = condition
    }

    onMount() {
        if (this.condition) {
            this.source.mount(this, this.parentE, this.el)
        }
    }

    onUnmount() {
        if (this.condition) {
            this.source.unmount()
        }
    }

    onMorph(other: Entity) {
        let newCond = (other as If).condition

        if (newCond != this.condition) {
            if (newCond) {
                this.source.mount(this, this.parentE, this.el)
            } else {
                this.source.onUnmount()
            }

            this.condition = newCond
        }
    }

    resolveRelativeNodes(): [Node, Node] {
        if (this.condition) {
            return this.source.resolveRelativeNodes()
        } else {
            return [undefined, undefined];
        }
    }

    resolveNextNodeOf(child: Entity): Node {
        return this.parent.resolveNextNodeOf(this)
    }
}

window['StackWeb'] = {
    Entity,
    Root,
    Group,
    Dom,
    Text,
    HelloWorld,
    Component,
    Invoke,
    If,
}
