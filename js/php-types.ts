
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

}
