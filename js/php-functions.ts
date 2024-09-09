/**
 * README
 *
 * This file exports php functions and do the same logic with php.
 * All the functions should be lowercase (cause of php function resolver logic).
 * Function arguments should contain `$params: Params` parameters.
 */
import {Params} from "./php";

export function gettype($params: Params) {
    let value = $params.next('value')
    $params.end()

    if (value === null) {
        return "null"
    }

    switch (typeof value) {
        case "undefined":
            return "null"

        case "number":
            return "float"

        case "bigint":
            return "int"

        case "boolean":
            return "bool"

        case "string":
            return "string"

        case "object":
        case "function":
        case "symbol":
        default:
            return "object"
    }
}

export function is_int($params: Params) {
    let value = $params.next('value')
    $params.end()

    return typeof value === "bigint"
}

export const is_integer = is_int

export function is_float($params: Params) {
    let value = $params.next('value')
    $params.end()

    return typeof value === "number"
}

export const is_double = is_float

export function is_bool($params: Params) {
    let value = $params.next('value')
    $params.end()

    return typeof value === "boolean"
}

export function is_null($params: Params) {
    let value = $params.next('value')
    $params.end()

    return value === undefined || value === null
}

export function is_object($params: Params) {
    let value = $params.next('value')
    $params.end()

    return gettype(value) === "object"
}

export function is_string($params: Params) {
    let value = $params.next('value')
    $params.end()

    return typeof value === "string"
}
