import * as PhpFunctions from './php-functions'
import {PHPArray, PHPRef} from "./php-types";
import {PHPString} from "./php-strings";

export class Scope {

    $static: string
    $this: Object
    vars: Object

    // @ts-ignore
    v: Proxy

    constructor($static: string = undefined, $this: Object = undefined, vars: Object = {}) {
        this.$static = $static
        this.$this = $this
        this.vars = vars

        // @ts-ignore
        this.v = new Proxy<Scope>(this, {
            get(target: Scope, p: string | symbol, receiver: any): any {
                if (p == 'this') {
                    return target.$this
                }

                let value = target.vars[p]

                if (value instanceof PHPRef) {
                    return value.get()
                }

                return value
            },

            set(target: Scope, p: string | symbol, newValue: any, receiver: any): boolean {
                if (target.vars[p] instanceof PHPRef) {
                    target.vars[p].set(newValue)
                    return true
                }

                target.vars[p] = newValue
                return true
            },

            has(target: Scope, p: string | symbol): boolean {
                return target.vars[p] !== undefined
            },
        })
    }

    ref(name: any) {
        name = PHPUtils.toString(name)
        let value = this.vars[name]

        if (value instanceof PHPRef) {
            return value
        }

        return this.vars[name] = new PHPRef(value)
    }

    real(name: any) {
        name = PHPUtils.toString(name)
        return this.vars[name]
    }

}

export class Params {

    $this: Object
    $static: string

    values: Object
    i: number

    constructor(values: Object, $this: Object = undefined, $static: string = undefined) {
        this.$this = $this
        this.$static = $static
        this.values = values
        this.i = 0
    }

    next(name: string, defaults : null | number | string | (() => any) = undefined): any {
        if (this.values[name] !== undefined) {
            let value = this.values[name]
            delete this.values[name]

            return value
        }

        if (this.values[this.i] !== undefined) {
            let value = this.values[this.i++]
            delete this.values[name]

            return value
        }

        if (defaults instanceof Function) {
            return defaults()
        }

        if (defaults !== undefined) {
            return defaults
        }

        throw new Error(`Parameter ${name} not passed`)
    }

    end() {
    }

}

export class PHPUtils {

    /**
     * Operator for +
     *
     * @param left
     * @param right
     */
    static opAdd(left: any, right: any) {
        if (left instanceof PHPArray) {
            let newArray = left.copy()
            newArray.plus(this.toArray(right))

            return newArray
        }

        return this.toNumber(left) + this.toNumber(right)
    }

    /**
     * Operator for -
     *
     * @param left
     * @param right
     */
    static opSub(left: any, right: any) {
        return this.toNumber(left) - this.toNumber(right)
    }

    /**
     * Operator for *
     *
     * @param left
     * @param right
     */
    static opMul(left: any, right: any) {
        return this.toNumber(left) * this.toNumber(right)
    }

    /**
     * Operator for /
     *
     * @param left
     * @param right
     */
    static opDiv(left: any, right: any) {
        return this.toNumber(left) / this.toNumber(right)
    }

    /**
     * Operator for .
     *
     * @param left
     * @param right
     */
    static opDot(left: any, right: any) {
        return this.toString(left) + this.toString(right)
    }

    static getType(value: any): string {
        if (value instanceof PHPRef) {
            value = value.get()
        }

        switch (typeof value) {
            case "undefined":
                return "null"

            case "number":
                return "double"

            case "bigint":
                return "integer"

            case "boolean":
                return "boolean"

            case "string":
                return "string"

            case "object":
                if (value === null) {
                    return "null"
                }

                if (value instanceof PHPArray) {
                    return "array"
                }

                if (value instanceof Array) {
                    return "array"
                }

                return "object"

            case "function":
            case "symbol":
            default:
                return "object"
        }
    }

    static toNumber(value: any): number {
        if (value instanceof PHPRef) {
            value = value.get()
        }

        let type = typeof value

        switch (type)
        {
            case "bigint":
            case "number":
                return value

            case "boolean":
                return value ? 1 : 0

            case "undefined":
                return 0

            case "object":
                return value === null ? 1 : 0

            case "string":
                let num = +value
                return isNaN(num) ? 0 : num

            default:
                return 1
        }
    }

    static toString(value: any): string {
        if (value instanceof PHPRef) {
            value = value.get()
        }

        let type = typeof value

        switch (type)
        {
            case "bigint":
            case "number":
                return '' + value

            case "string":
                return value

            case "boolean":
                return value ? '1' : ''

            case "undefined":
                return ''

            case "object":
                if (value === null) {
                    return ''
                }

                if (value instanceof PHPArray) {
                    return "Array"
                }

                return 'object'

            default:
                return 'object'
        }
    }

    static toBool(value: any): boolean {
        if (value instanceof PHPRef) {
            value = value.get()
        }

        let type = typeof value

        switch (type)
        {
            case "bigint":
            case "number":
                return value != 0;

            case "string":
                return value != "" && value != "0"

            case "boolean":
                return value

            case "undefined":
                return false

            case "object":
                if (value instanceof PHPArray) {
                    return value.count() > 0
                }

                return value !== null

            default:
                return true
        }
    }

    static isNumeric(value: any): boolean {
        if (value instanceof PHPRef) {
            value = value.get()
        }

        let type = typeof value

        if (type == "bigint" || type == "number") {
            return true
        }

        if (type != "string") {
            return false
        }

        return !isNaN(value) && !isNaN(+value)
    }

    static toStringOrNumber(value: any): string | number {
        if (typeof value === 'number') {
            return value
        }

        return this.toString(value)
    }

    static toArray(value: any): PHPArray {
        if (value instanceof PHPRef) {
            value = value.get()
        }

        let type = typeof value

        if (value instanceof PHPArray) {
            return value
        }

        switch (type)
        {
            case "string":
                return PHPArray.fromArray(new Array<string>(...value))

            default:
                return PHPArray.fromEmpty()
        }
    }

    static callFunction(name: string, params: Params) {
        name = name.toLowerCase()

        let fn = Shared.functions[name]
        if (fn) {
            return this.callJsFunction(fn, params)
        }
        else {
            throw new Error(`Function [${name}] is not exists`)
        }
    }

    static callJsFunction(func: Function, params: Params) {
        return func(params)
    }

    static getOffset(arrayAccess: any, offset: any) {
        offset = PHPUtils.toStringOrNumber(offset)

        switch (typeof arrayAccess) {
            case "string":
                return Shared.defaultString.substr(arrayAccess, PHPUtils.toNumber(offset), 1)

            case "object":
                if (arrayAccess instanceof PHPArray) {
                    return arrayAccess.get(offset)
                }
                break
        }

        return null
    }

    static setOffset(arrayAccess: any, offset: any, value: any) {
        offset = PHPUtils.toStringOrNumber(offset)

        switch (typeof arrayAccess) {
            case "object":
                if (arrayAccess instanceof PHPArray) {
                    arrayAccess.set(offset, value)
                    return
                }
                break
        }

        return null
    }

    static pushOffset(arrayAccess: any, value: any) {
        switch (typeof arrayAccess) {
            case "object":
                if (arrayAccess instanceof PHPArray) {
                    arrayAccess.push(value)
                    return
                }
                break
        }

        return null
    }

    static getArrayAccess(scope: any, offset: any) {
        if (offset !== null) {
            offset = PHPUtils.toStringOrNumber(offset)
        }

        if (scope instanceof Scope) {
            if (offset === null) {
                offset = ""
            }

            let r = scope.real(offset)

            if (r === undefined || r === null) {
                scope.v[offset] = r = PHPArray.fromEmpty()
            }

            return r
        }
        else if (scope instanceof PHPArray) {
            let r
            if (offset === null) {
                scope.push(r = PHPArray.fromEmpty())
            }
            else {
                r = scope.getReal(offset)

                if (r === undefined || r === null) {
                    scope.set(offset, r = PHPArray.fromEmpty())
                }
            }

            return r
        }

        return null
    }

}

export let PHP = {
}

let textEncoder = new TextEncoder()
let textDecoder = new TextDecoder()
let defaultString = new PHPString(textEncoder, textDecoder)

export let Shared = {
    functions: PhpFunctions,
    textEncoder,
    textDecoder,
    defaultString,
}

// @ts-ignore
window.P = PHP
// @ts-ignore
window.PHPUtils = PHPUtils

// @ts-ignore
window.Test = () => {
    let local: Scope = new Scope(), v = local.v

    PHPUtils.setOffset(PHPUtils.getArrayAccess(PHPUtils.getArrayAccess(local, 'a'), null), 0, "Hi")

    console.log(v.a)
}
