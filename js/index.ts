abstract class Entity {

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
        this.morph(other)
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

class Root extends Entity {
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

class Group extends Entity {
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

interface DomRegister {
    name: string
    attrs: Object
    slot: Group
}

class Dom extends Entity {
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

class Text extends Entity {
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

class HelloWorld extends Entity {
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

interface ComponentRegister {
    states: ($: Component) => Object
    slot: ($: Component) => Group
}

class Component extends Entity {
    source: ComponentRegister
    states = {}
    slot: Group

    constructor(source: ComponentRegister) {
        super()
        this.source = source
    }

    onMount() {
        this.states = this.source.states(this)
        this.slot = this.source.slot(this)

        this.slot.mount(this, this.parentE, this.el)
    }

    onUnmount() {
        this.slot.unmount()
    }

    reset() {
        this.states = this.source.states(this)
    }

    changed: boolean = false

    refresh() {
        const newSlot = this.source.slot(this)

        this.slot.morph(newSlot)

        this.changed = false
    }

    onMorph(other: Entity) {
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

    resolveRelativeNodes(): [Node, Node] {
        return this.slot.resolveRelativeNodes()
    }

    resolveNextNodeOf(child: Entity): Node {
        return this.parent.resolveNextNodeOf(this)
    }
}

class If extends Entity {
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

let StackWeb = {
    Entity,
    Root,
    Group,
    Dom,
    Text,
    HelloWorld,
    Component,
    If,
}

window['StackWeb'] = StackWeb

export default StackWeb
