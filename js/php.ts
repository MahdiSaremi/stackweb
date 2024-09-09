import * as PhpFunctions from './php-functions'

export class Scope {

    $static: string
    $this: Object
    vars: Object

    constructor($static: string, $this: Object, vars: Object = {}) {
        this.$static = $static
        this.$this = $this
        this.vars = vars

        // @ts-ignore
        this.v = new Proxy<Scope>(this, {
            get(target: Scope, p: string | symbol, receiver: any): any {
                if (p == 'this') {
                    return target.$this
                }

                return target.vars[p]
            },

            set(target: Scope, p: string | symbol, newValue: any, receiver: any): boolean {
                target.vars[p] = newValue
                return true
            },

            has(target: Scope, p: string | symbol): boolean {
                return target.vars[p] !== undefined
            },
        })
    }



}

export class Params {

    $this: Object
    $static: string

    values: Object
    i: number

    constructor(values: Object, $this: Object, $static: string) {
        this.$this = $this
        this.$static = $static
        this.values = values
        this.i = 0
    }

    next(name: string, defaults : () => any = undefined): any {
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

        if (defaults) {
            return defaults()
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

    }

    /**
     * Operator for -
     *
     * @param left
     * @param right
     */
    static opSub(left: any, right: any) {

    }

    /**
     * Operator for *
     *
     * @param left
     * @param right
     */
    static opMul(left: any, right: any) {

    }

    /**
     * Operator for /
     *
     * @param left
     * @param right
     */
    static opDiv(left: any, right: any) {

    }

    /**
     * Operator for .
     *
     * @param left
     * @param right
     */
    static opDot(left: any, right: any) {

    }

    static isInt(left: any, right: any) {

    }

}

export let PHP = {
    Functions: PhpFunctions,
}

// @ts-ignore
window.PHP = PHP
