/**
 * README
 *
 * This file exports php functions and do the same logic with php.
 * All the functions should be lowercase (cause of php function resolver logic).
 * Function arguments should contain `$params: Params` parameters.
 */
import {Params, PHPUtils} from "./php";

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
