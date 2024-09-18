/**
 * README
 *
 * This file exports php functions and do the same logic with php.
 * All the functions should be lowercase (cause of php function resolver logic).
 * Function arguments should contain `$params: Params` parameters.
 */
import {Params, PHPUtils, Shared} from "./php";
import {PHPArray} from "./php-types";
import {PHPString} from "./php-strings";

export function gettype($params: Params) {
    let value = $params.next('value')
    $params.end()

    return PHPUtils.getType(value)
}

// export function is_int($params: Params) {
//     let value = $params.next('value')
//     $params.end()
//
//     return typeof value === "bigint"
// }

// export const is_integer = is_int

export function is_float($params: Params) {
    let value = $params.next('value')
    $params.end()

    return PHPUtils.getType(value) === "double"
}

export const is_double = is_float

export function is_bool($params: Params) {
    let value = $params.next('value')
    $params.end()

    return PHPUtils.getType(value) === "boolean"
}

export function is_null($params: Params) {
    let value = $params.next('value')
    $params.end()

    return PHPUtils.getType(value) === "null"
}

export function is_object($params: Params) {
    let value = $params.next('value')
    $params.end()

    return PHPUtils.getType(value) === "object"
}

export function is_string($params: Params) {
    let value = $params.next('value')
    $params.end()

    return PHPUtils.getType(value) === "string"
}

export function is_array($params: Params) {
    let value = $params.next('value')
    $params.end()

    return value instanceof PHPArray
}

export function strlen($params: Params) {
    let string = PHPUtils.toString($params.next('string'))
    $params.end()

    return Shared.defaultString.len(string)
}

export function substr($params: Params) {
    let string = PHPUtils.toString($params.next('string'))
    let offset = PHPUtils.toNumber($params.next('offset'))
    let length = $params.next('length', null)
    $params.end()

    if (length !== null) {
        length = PHPUtils.toNumber(length)
    }

    return Shared.defaultString.substr(string, offset, length)
}

export function trim($params: Params) {
    let string = PHPUtils.toString($params.next('string'))
    let characters = PHPUtils.toString($params.next('characters', " \n\r\t\v\0"))
    $params.end()

    return Shared.defaultString.trim(string, characters)
}

export function ltrim($params: Params) {
    let string = PHPUtils.toString($params.next('string'))
    let characters = PHPUtils.toString($params.next('characters', " \n\r\t\v\0"))
    $params.end()

    return Shared.defaultString.ltrim(string, characters)
}

export function rtrim($params: Params) {
    let string = PHPUtils.toString($params.next('string'))
    let characters = PHPUtils.toString($params.next('characters', " \n\r\t\v\0"))
    $params.end()

    return Shared.defaultString.rtrim(string, characters)
}
