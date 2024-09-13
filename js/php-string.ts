import {Shared} from "./php";

export class PHPString {

    encoder: TextEncoder
    decoder: TextDecoder

    constructor(encoder: TextEncoder, decoder: TextDecoder) {
        this.encoder = encoder
        this.decoder = decoder
    }

    len(value: string) : number {
        let encoded = this.encoder.encode(value)
        let result = 0

        for (let i = 0; i < encoded.length; i++) {
            result += this.decoder.decode(encoded.slice(i, i + 1)).length
        }

        return result
    }

    substr(value: string, offset: number, length: number) {
        // todo
    }

    split(value: string): string[] {
        let encoded = this.encoder.encode(value)
        let result = new Array(encoded.length)

        for (let i = 0; i < encoded.length; i++) {
            result[i] = this.decoder.decode(encoded.slice(i, i + 1))
        }

        return result
    }

    splitCharsToString(value: string[]): string {
        let array = new Uint8Array(value.length)

        for (const key in value) {
            array[key] = this.encoder.encode(value[key])[0]
        }

        return this.decoder.decode(array)
    }

    splitToString(value: string[]): string {
        let array: Array<Uint8Array> = new Array(value.length)
        let length = 0

        for (const key in value) {
            array[key] = this.encoder.encode(value[key])
            length += array[key].length
        }

        let out = new Uint8Array(length)

        let i = 0
        for (const key in array) {
            for (const key2 in array[key]) {
                out[i++] = array[key][key2]
            }
        }

        return this.decoder.decode(out)
    }

}
