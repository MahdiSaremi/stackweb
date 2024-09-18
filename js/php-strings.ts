import {Shared} from "./php";

export class PHPString {

    encoder: TextEncoder
    decoder: TextDecoder

    constructor(encoder: TextEncoder, decoder: TextDecoder) {
        this.encoder = encoder
        this.decoder = decoder
    }

    standardSubParams(offset: number, length: number | null, strLength: number): [number, number | null] {
        while (offset < 0) {
            offset = strLength + offset
        }

        if (offset >= strLength) {
            return [0, 0]
        }

        if (length !== null) {
            if (length < 0) {
                return [0, 0]
            }

            let end = offset + length
            if (end >= strLength) {
                length -= end - strLength
            }
        }

        return [offset, length]
    }

    len(value: string) : number {
        let encoded = this.encoder.encode(value)
        let result = 0

        for (let i = 0; i < encoded.length; i++) {
            result += this.decoder.decode(encoded.slice(i, i + 1)).length
        }

        return result
    }

    substr(value: string, offset: number, length: number | null) {
        let encoded = this.encoder.encode(value)

        ;[offset, length] = this.standardSubParams(offset, length, encoded.length)

        let slice: Uint8Array
        if (length === null) {
            slice = encoded.slice(offset, encoded.length)
        } else {
            slice = encoded.slice(offset, offset + length)
        }

        return this.decoder.decode(slice)
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

    trim(value: string, characters: string) {
        let str = this.encoder.encode(value)
        let chars = this.encoder.encode(characters)

        let start, end

        for (start = 0; start < str.length; start++) {
            if (chars.indexOf(str[start]) < 0) {
                break
            }
        }

        for (end = str.length - 1; end > start; end--) {
            if (chars.indexOf(str[end]) < 0) {
                break
            }
        }

        return this.decoder.decode(str.slice(start, end + 1))
    }

    ltrim(value: string, characters: string) {
        let str = this.encoder.encode(value)
        let chars = this.encoder.encode(characters)

        let start

        for (start = 0; start < str.length; start++) {
            if (chars.indexOf(str[start]) < 0) {
                break
            }
        }

        return this.decoder.decode(str.slice(start, str.length))
    }

    rtrim(value: string, characters: string) {
        let str = this.encoder.encode(value)
        let chars = this.encoder.encode(characters)

        let end

        for (end = str.length - 1; end >= 0; end--) {
            if (chars.indexOf(str[end]) < 0) {
                break
            }
        }

        return this.decoder.decode(str.slice(0, end + 1))
    }

}
