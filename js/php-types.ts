import {PHPUtils} from "./php";

export class PHPRef {
    private value: any

    constructor(value: any = undefined) {
        this.value = value
    }

    set(value: any) {
        if (this.value instanceof PHPRef) {
            return this.value.set(value)
        }

        this.value = value
    }

    get() {
        if (this.value instanceof PHPRef) {
            return this.value.get()
        }

        return this.value
    }
}

export class PHPArray {

    // @ts-ignore
    public map: Map

    public keys: Array<number | string>

    public high: number

    // @ts-ignore
    constructor(map: Map, keys: Array<number | string>, high: number) {
        this.map = map
        this.keys = keys
        this.high = high
    }

    static fromEmpty(): PHPArray {
        // @ts-ignore
        return new PHPArray(new Map(), [], -1)
    }

    static fromArray(array: Array<any>): PHPArray {
        // @ts-ignore
        let map = new Map()
        let keys = []
        array.forEach((value, index) => {
            map.set(index, value)
            keys.push(index)
        })

        return new PHPArray(map, keys, array.length - 1)
    }

    // @ts-ignore
    static fromMap(map: Map<string | number, any>): PHPArray {
        let keys = []
        let high = -1
        for (const key in map.keys()) {
            keys.push(key)
            if (typeof key == "number" && key > high) {
                high = key
            }
        }

        return new PHPArray(map, keys, high)
    }

    static fromObject(object: Object): PHPArray {
        // @ts-ignore
        let map = new Map()
        let keys = []
        let high = -1
        for (let key in object) {
            let num = +key
            if (!isNaN(num)) {
                // @ts-ignore
                key = num
            }

            map.set(key, object[key])
            keys.push(key)

            if (typeof key == "number" && key > high) {
                high = key
            }
        }

        return new PHPArray(map, keys, high)
    }

    copy(): PHPArray {
        // @ts-ignore
        return new PHPArray(new Map(this.map), [...this.keys], this.high)
    }

    push(value: any) {
        this.high++

        if (this.map.has(this.high)) {
            this.map.set(this.high, value)
            return
        }

        this.map.set(this.high, value)
        this.keys.push(this.high)
    }

    pop() {
        if (this.keys.length == 0) {
            return null
        }

        let last = this.keys.pop()
        let pop = this.map.get(last)
        this.map.delete(last)

        if (last === this.high) {
            this.high--
        }

        return pop
    }

    setReal(key: string | number, value: any) {
        if (typeof key == "string") {
            let num = +key
            if (!isNaN(num)) {
                key = num
            }
        }

        if (typeof key == "number") {
            if (key == this.high + 1) {
                this.push(value)
                return
            } else if (key > this.high) {
                this.high = key
            }
        }

        if (this.map.has(key)) {
            this.map.set(key, value)
            return
        }

        this.map.set(key, value)
        this.keys.push(key)
    }

    set(key: string | number, value: any) {
        if (typeof key == "string") {
            let num = +key
            if (!isNaN(num)) {
                key = num
            }
        }

        let old = this.map.get(key)

        if (old instanceof PHPRef) {
            old.set(value)
        }

        this.setReal(key, value)
    }

    getReal(key: string | number) {
        if (typeof key == "string") {
            let num = +key
            if (!isNaN(num)) {
                key = num
            }
        }

        return this.map.get(key)
    }

    get(key: string | number) {
        let value = this.getReal(key)

        if (value instanceof PHPRef) {
            return value.get()
        }

        return value
    }

    plus(array: PHPArray) {
        this.high = this.high > array.high ? this.high : array.high
        array.map.forEach((value, key) => {
            if (!this.map.has(key)) {
                this.map.set(key, value)
                this.keys.push(key)
            }
        })
    }

    replace(array: PHPArray) {
        this.high = this.high > array.high ? this.high : array.high
        array.map.forEach((value, key) => {
            this.set(key, value)
        })
    }

    merge(array: PHPArray) {
        this.high = this.high > array.high ? this.high : array.high
        array.map.forEach((value, key) => {
            if (isNaN(+key)) {
                this.set(key, value)
            } else {
                this.push(value)
            }
        })
    }

    count() {
        return this.keys.length
    }

    ref(key: string | number) {
        let value = this.get(key)
        let ref = new PHPRef(value)
        this.set(key, ref)

        return ref
    }

}
