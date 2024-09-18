var __defProp = Object.defineProperty;
var __export = (target, all) => {
  for (var name in all)
    __defProp(target, name, { get: all[name], enumerable: true });
};

// js/php-functions.ts
var php_functions_exports = {};
__export(php_functions_exports, {
  gettype: () => gettype,
  is_array: () => is_array,
  is_bool: () => is_bool,
  is_double: () => is_double,
  is_float: () => is_float,
  is_null: () => is_null,
  is_object: () => is_object,
  is_string: () => is_string,
  ltrim: () => ltrim,
  rtrim: () => rtrim,
  strlen: () => strlen,
  substr: () => substr,
  trim: () => trim
});

// js/php-types.ts
var PHPRef = class {
  constructor(value = void 0) {
    this.value = value;
  }
  set(value) {
    if (this.value instanceof PHPRef) {
      return this.value.set(value);
    }
    this.value = value;
  }
  get() {
    if (this.value instanceof PHPRef) {
      return this.value.get();
    }
    return this.value;
  }
};
var PHPArray = class {
  constructor(map, keys, high) {
    this.map = map;
    this.keys = keys;
    this.high = high;
  }
  static fromEmpty() {
    return new PHPArray(/* @__PURE__ */ new Map(), [], -1);
  }
  static fromArray(array) {
    let map = /* @__PURE__ */ new Map();
    let keys = [];
    array.forEach((value, index) => {
      map.set(index, value);
      keys.push(index);
    });
    return new PHPArray(map, keys, array.length - 1);
  }
  static fromMap(map) {
    let keys = [];
    let high = -1;
    for (const key in map.keys()) {
      keys.push(key);
      if (typeof key == "number" && key > high) {
        high = key;
      }
    }
    return new PHPArray(map, keys, high);
  }
  static fromObject(object) {
    let map = /* @__PURE__ */ new Map();
    let keys = [];
    let high = -1;
    for (let key in object) {
      let num = +key;
      if (!isNaN(num)) {
        key = num;
      }
      map.set(key, object[key]);
      keys.push(key);
      if (typeof key == "number" && key > high) {
        high = key;
      }
    }
    return new PHPArray(map, keys, high);
  }
  copy() {
    return new PHPArray(new Map(this.map), [...this.keys], this.high);
  }
  push(value) {
    this.high++;
    if (this.map.has(this.high)) {
      this.map.set(this.high, value);
      return;
    }
    this.map.set(this.high, value);
    this.keys.push(this.high);
  }
  pop() {
    if (this.keys.length == 0) {
      return null;
    }
    let last = this.keys.pop();
    let pop = this.map.get(last);
    this.map.delete(last);
    if (last === this.high) {
      this.high--;
    }
    return pop;
  }
  setReal(key, value) {
    if (typeof key == "string") {
      let num = +key;
      if (!isNaN(num)) {
        key = num;
      }
    }
    if (typeof key == "number") {
      if (key == this.high + 1) {
        this.push(value);
        return;
      } else if (key > this.high) {
        this.high = key;
      }
    }
    if (this.map.has(key)) {
      this.map.set(key, value);
      return;
    }
    this.map.set(key, value);
    this.keys.push(key);
  }
  set(key, value) {
    if (typeof key == "string") {
      let num = +key;
      if (!isNaN(num)) {
        key = num;
      }
    }
    let old = this.map.get(key);
    if (old instanceof PHPRef) {
      old.set(value);
    }
    this.setReal(key, value);
  }
  getReal(key) {
    if (typeof key == "string") {
      let num = +key;
      if (!isNaN(num)) {
        key = num;
      }
    }
    return this.map.get(key);
  }
  get(key) {
    let value = this.getReal(key);
    if (value instanceof PHPRef) {
      return value.get();
    }
    return value;
  }
  plus(array) {
    this.high = this.high > array.high ? this.high : array.high;
    array.map.forEach((value, key) => {
      if (!this.map.has(key)) {
        this.map.set(key, value);
        this.keys.push(key);
      }
    });
  }
  replace(array) {
    this.high = this.high > array.high ? this.high : array.high;
    array.map.forEach((value, key) => {
      this.set(key, value);
    });
  }
  merge(array) {
    this.high = this.high > array.high ? this.high : array.high;
    array.map.forEach((value, key) => {
      if (isNaN(+key)) {
        this.set(key, value);
      } else {
        this.push(value);
      }
    });
  }
  count() {
    return this.keys.length;
  }
  ref(key) {
    let value = this.get(key);
    let ref = new PHPRef(value);
    this.set(key, ref);
    return ref;
  }
};

// js/php-functions.ts
function gettype($params) {
  let value = $params.next("value");
  $params.end();
  return PHPUtils.getType(value);
}
function is_float($params) {
  let value = $params.next("value");
  $params.end();
  return PHPUtils.getType(value) === "double";
}
var is_double = is_float;
function is_bool($params) {
  let value = $params.next("value");
  $params.end();
  return PHPUtils.getType(value) === "boolean";
}
function is_null($params) {
  let value = $params.next("value");
  $params.end();
  return PHPUtils.getType(value) === "null";
}
function is_object($params) {
  let value = $params.next("value");
  $params.end();
  return PHPUtils.getType(value) === "object";
}
function is_string($params) {
  let value = $params.next("value");
  $params.end();
  return PHPUtils.getType(value) === "string";
}
function is_array($params) {
  let value = $params.next("value");
  $params.end();
  return value instanceof PHPArray;
}
function strlen($params) {
  let string = PHPUtils.toString($params.next("string"));
  $params.end();
  return Shared.defaultString.len(string);
}
function substr($params) {
  let string = PHPUtils.toString($params.next("string"));
  let offset = PHPUtils.toNumber($params.next("offset"));
  let length = $params.next("length", null);
  $params.end();
  if (length !== null) {
    length = PHPUtils.toNumber(length);
  }
  return Shared.defaultString.substr(string, offset, length);
}
function trim($params) {
  let string = PHPUtils.toString($params.next("string"));
  let characters = PHPUtils.toString($params.next("characters", " \n\r	\v\0"));
  $params.end();
  return Shared.defaultString.trim(string, characters);
}
function ltrim($params) {
  let string = PHPUtils.toString($params.next("string"));
  let characters = PHPUtils.toString($params.next("characters", " \n\r	\v\0"));
  $params.end();
  return Shared.defaultString.ltrim(string, characters);
}
function rtrim($params) {
  let string = PHPUtils.toString($params.next("string"));
  let characters = PHPUtils.toString($params.next("characters", " \n\r	\v\0"));
  $params.end();
  return Shared.defaultString.rtrim(string, characters);
}

// js/php-strings.ts
var PHPString = class {
  constructor(encoder, decoder) {
    this.encoder = encoder;
    this.decoder = decoder;
  }
  standardSubParams(offset, length, strLength) {
    while (offset < 0) {
      offset = strLength + offset;
    }
    if (offset >= strLength) {
      return [0, 0];
    }
    if (length !== null) {
      if (length < 0) {
        return [0, 0];
      }
      let end = offset + length;
      if (end >= strLength) {
        length -= end - strLength;
      }
    }
    return [offset, length];
  }
  len(value) {
    let encoded = this.encoder.encode(value);
    let result = 0;
    for (let i = 0; i < encoded.length; i++) {
      result += this.decoder.decode(encoded.slice(i, i + 1)).length;
    }
    return result;
  }
  substr(value, offset, length) {
    let encoded = this.encoder.encode(value);
    [offset, length] = this.standardSubParams(offset, length, encoded.length);
    let slice;
    if (length === null) {
      slice = encoded.slice(offset, encoded.length);
    } else {
      slice = encoded.slice(offset, offset + length);
    }
    return this.decoder.decode(slice);
  }
  split(value) {
    let encoded = this.encoder.encode(value);
    let result = new Array(encoded.length);
    for (let i = 0; i < encoded.length; i++) {
      result[i] = this.decoder.decode(encoded.slice(i, i + 1));
    }
    return result;
  }
  splitCharsToString(value) {
    let array = new Uint8Array(value.length);
    for (const key in value) {
      array[key] = this.encoder.encode(value[key])[0];
    }
    return this.decoder.decode(array);
  }
  splitToString(value) {
    let array = new Array(value.length);
    let length = 0;
    for (const key in value) {
      array[key] = this.encoder.encode(value[key]);
      length += array[key].length;
    }
    let out = new Uint8Array(length);
    let i = 0;
    for (const key in array) {
      for (const key2 in array[key]) {
        out[i++] = array[key][key2];
      }
    }
    return this.decoder.decode(out);
  }
  trim(value, characters) {
    let str = this.encoder.encode(value);
    let chars = this.encoder.encode(characters);
    let start, end;
    for (start = 0; start < str.length; start++) {
      if (chars.indexOf(str[start]) < 0) {
        break;
      }
    }
    for (end = str.length - 1; end > start; end--) {
      if (chars.indexOf(str[end]) < 0) {
        break;
      }
    }
    return this.decoder.decode(str.slice(start, end + 1));
  }
  ltrim(value, characters) {
    let str = this.encoder.encode(value);
    let chars = this.encoder.encode(characters);
    let start;
    for (start = 0; start < str.length; start++) {
      if (chars.indexOf(str[start]) < 0) {
        break;
      }
    }
    return this.decoder.decode(str.slice(start, str.length));
  }
  rtrim(value, characters) {
    let str = this.encoder.encode(value);
    let chars = this.encoder.encode(characters);
    let end;
    for (end = str.length - 1; end >= 0; end--) {
      if (chars.indexOf(str[end]) < 0) {
        break;
      }
    }
    return this.decoder.decode(str.slice(0, end + 1));
  }
};

// js/php.ts
var Scope = class {
  constructor($static = void 0, $this = void 0, vars = {}) {
    this.$static = $static;
    this.$this = $this;
    this.vars = vars;
    this.v = new Proxy(this, {
      get(target, p, receiver) {
        if (p == "this") {
          return target.$this;
        }
        let value = target.vars[p];
        if (value instanceof PHPRef) {
          return value.get();
        }
        return value;
      },
      set(target, p, newValue, receiver) {
        if (target.vars[p] instanceof PHPRef) {
          target.vars[p].set(newValue);
          return true;
        }
        target.vars[p] = newValue;
        return true;
      },
      has(target, p) {
        return target.vars[p] !== void 0;
      }
    });
  }
  ref(name) {
    name = PHPUtils.toString(name);
    let value = this.vars[name];
    if (value instanceof PHPRef) {
      return value;
    }
    return this.vars[name] = new PHPRef(value);
  }
  real(name) {
    name = PHPUtils.toString(name);
    return this.vars[name];
  }
};
var PHPUtils = class {
  static opAdd(left, right) {
    if (left instanceof PHPArray) {
      let newArray = left.copy();
      newArray.plus(this.toArray(right));
      return newArray;
    }
    return this.toNumber(left) + this.toNumber(right);
  }
  static opSub(left, right) {
    return this.toNumber(left) - this.toNumber(right);
  }
  static opMul(left, right) {
    return this.toNumber(left) * this.toNumber(right);
  }
  static opDiv(left, right) {
    return this.toNumber(left) / this.toNumber(right);
  }
  static opDot(left, right) {
    return this.toString(left) + this.toString(right);
  }
  static getType(value) {
    if (value instanceof PHPRef) {
      value = value.get();
    }
    switch (typeof value) {
      case "undefined":
        return "null";
      case "number":
        return "double";
      case "bigint":
        return "integer";
      case "boolean":
        return "boolean";
      case "string":
        return "string";
      case "object":
        if (value === null) {
          return "null";
        }
        if (value instanceof PHPArray) {
          return "array";
        }
        if (value instanceof Array) {
          return "array";
        }
        return "object";
      case "function":
      case "symbol":
      default:
        return "object";
    }
  }
  static toNumber(value) {
    if (value instanceof PHPRef) {
      value = value.get();
    }
    let type = typeof value;
    switch (type) {
      case "bigint":
      case "number":
        return value;
      case "boolean":
        return value ? 1 : 0;
      case "undefined":
        return 0;
      case "object":
        return value === null ? 1 : 0;
      case "string":
        let num = +value;
        return isNaN(num) ? 0 : num;
      default:
        return 1;
    }
  }
  static toString(value) {
    if (value instanceof PHPRef) {
      value = value.get();
    }
    let type = typeof value;
    switch (type) {
      case "bigint":
      case "number":
        return "" + value;
      case "string":
        return value;
      case "boolean":
        return value ? "1" : "";
      case "undefined":
        return "";
      case "object":
        if (value === null) {
          return "";
        }
        if (value instanceof PHPArray) {
          return "Array";
        }
        return "object";
      default:
        return "object";
    }
  }
  static toBool(value) {
    if (value instanceof PHPRef) {
      value = value.get();
    }
    let type = typeof value;
    switch (type) {
      case "bigint":
      case "number":
        return value != 0;
      case "string":
        return value != "" && value != "0";
      case "boolean":
        return value;
      case "undefined":
        return false;
      case "object":
        if (value instanceof PHPArray) {
          return value.count() > 0;
        }
        return value !== null;
      default:
        return true;
    }
  }
  static isNumeric(value) {
    if (value instanceof PHPRef) {
      value = value.get();
    }
    let type = typeof value;
    if (type == "bigint" || type == "number") {
      return true;
    }
    if (type != "string") {
      return false;
    }
    return !isNaN(value) && !isNaN(+value);
  }
  static toStringOrNumber(value) {
    if (typeof value === "number") {
      return value;
    }
    return this.toString(value);
  }
  static toArray(value) {
    if (value instanceof PHPRef) {
      value = value.get();
    }
    let type = typeof value;
    if (value instanceof PHPArray) {
      return value;
    }
    switch (type) {
      case "string":
        return PHPArray.fromArray(new Array(...value));
      default:
        return PHPArray.fromEmpty();
    }
  }
  static callFunction(name, params) {
    name = name.toLowerCase();
    let fn = Shared.functions[name];
    if (fn) {
      return this.callJsFunction(fn, params);
    } else {
      throw new Error(`Function [${name}] is not exists`);
    }
  }
  static callJsFunction(func, params) {
    return func(params);
  }
  static getOffset(arrayAccess, offset) {
    offset = PHPUtils.toStringOrNumber(offset);
    switch (typeof arrayAccess) {
      case "string":
        return Shared.defaultString.substr(arrayAccess, PHPUtils.toNumber(offset), 1);
      case "object":
        if (arrayAccess instanceof PHPArray) {
          return arrayAccess.get(offset);
        }
        break;
    }
    return null;
  }
  static setOffset(arrayAccess, offset, value) {
    offset = PHPUtils.toStringOrNumber(offset);
    switch (typeof arrayAccess) {
      case "object":
        if (arrayAccess instanceof PHPArray) {
          arrayAccess.set(offset, value);
          return;
        }
        break;
    }
    return null;
  }
  static pushOffset(arrayAccess, value) {
    switch (typeof arrayAccess) {
      case "object":
        if (arrayAccess instanceof PHPArray) {
          arrayAccess.push(value);
          return;
        }
        break;
    }
    return null;
  }
  static getArrayAccess(scope, offset) {
    if (offset !== null) {
      offset = PHPUtils.toStringOrNumber(offset);
    }
    if (scope instanceof Scope) {
      if (offset === null) {
        offset = "";
      }
      let r = scope.real(offset);
      if (r === void 0 || r === null) {
        scope.v[offset] = r = PHPArray.fromEmpty();
      }
      return r;
    } else if (scope instanceof PHPArray) {
      let r;
      if (offset === null) {
        scope.push(r = PHPArray.fromEmpty());
      } else {
        r = scope.getReal(offset);
        if (r === void 0 || r === null) {
          scope.set(offset, r = PHPArray.fromEmpty());
        }
      }
      return r;
    }
    return null;
  }
};
var PHP = {};
var textEncoder = new TextEncoder();
var textDecoder = new TextDecoder();
var defaultString = new PHPString(textEncoder, textDecoder);
var Shared = {
  functions: php_functions_exports,
  textEncoder,
  textDecoder,
  defaultString
};
window.P = PHP;
window.PHPUtils = PHPUtils;
window.Test = () => {
  let local = new Scope(), v = local.v;
  PHPUtils.setOffset(PHPUtils.getArrayAccess(PHPUtils.getArrayAccess(local, "a"), null), 0, "Hi");
  console.log(v.a);
};

// js/index.ts
var Entity = class {
  constructor() {
    this.isMounted = false;
    this.isStatic = false;
  }
  mount(parent, parentE, el) {
    this.parent = parent;
    this.parentE = parentE;
    this.el = el;
    this.isMounted = false;
    this.onMount();
    this.isMounted = true;
  }
  unmount() {
    this.onUnmount();
    this.isMounted = false;
  }
  morph(other) {
    if (this.isStatic)
      return;
    this.onMorph(other);
  }
  static() {
    this.isStatic = true;
    return this;
  }
  insertNode(node) {
    if (this.parentE.isMounted) {
      let nextOfMe = this.parent.resolveNextNodeOf(this);
      if (nextOfMe) {
        this.parent.el.insertBefore(node, nextOfMe);
      } else {
        this.parent.el.appendChild(node);
      }
    } else {
      this.parent.el.appendChild(node);
    }
  }
};
var Root = class extends Entity {
  constructor(source) {
    super();
    this.source = source;
  }
  onMount() {
    this.source.mount(this, this, this.el);
  }
  onUnmount() {
    this.source.unmount();
  }
  onMorph(other) {
    this.source.morph(other.source);
  }
  resolveRelativeNodes() {
    return this.source.resolveRelativeNodes();
  }
  resolveNextNodeOf(child) {
    return void 0;
  }
};
var Group = class extends Entity {
  constructor(source) {
    super();
    this.source = source;
  }
  onMount() {
    for (const i in this.source) {
      this.source[i].mount(this, this.parentE, this.el);
    }
  }
  onUnmount() {
    for (const i in this.source) {
      this.source[i].unmount();
    }
  }
  onMorph(other) {
    for (const i in this.source) {
      this.source[i].morph(other.source[i]);
    }
  }
  resolveRelativeNodes() {
    if (this.source.length == 0) {
      return [void 0, void 0];
    } else if (this.source.length == 1) {
      return this.source[0].resolveRelativeNodes();
    } else {
      let a, b, i;
      for (i = 0; i < this.source.length; i++) {
        let cur = this.source[i].resolveRelativeNodes();
        if (cur[0] || cur[1]) {
          a = cur[0] ?? cur[1];
          break;
        }
      }
      for (i = this.source.length - 1; i >= 0; i--) {
        let cur = this.source[i].resolveRelativeNodes();
        if (cur[0] || cur[1]) {
          b = cur[1] ?? cur[0];
          break;
        }
      }
      return [a, b];
    }
  }
  resolveNextNodeOf(child) {
    let index = this.source.indexOf(child);
    for (let i = index + 1; i < this.source.length; i++) {
      let cur = this.source[i].resolveRelativeNodes();
      if (cur[0] || cur[1]) {
        return cur[0] ?? cur[1];
      }
    }
    if (this.parent) {
      return this.parent.resolveNextNodeOf(this);
    }
    return void 0;
  }
};
var Dom = class extends Entity {
  constructor(source) {
    super();
    this.source = source;
    this.filterSource();
  }
  filterSource() {
    for (const key in this.source.attrs) {
      let value = this.source.attrs[key];
      if (value === void 0 || value === null || value === false) {
        delete this.source.attrs[key];
      }
    }
  }
  onMount() {
    this.el = document.createElement(this.source.name);
    this.insertNode(this.el);
    this.source.slot.mount(this, this, this.el);
    for (const key in this.source.attrs) {
      this._setAttribute(key, this.source.attrs[key], void 0);
    }
  }
  onUnmount() {
    this.el.remove();
    this.el = void 0;
  }
  onMorph(other) {
    this.source.slot.morph(other.source.slot);
    let newAttrs = other.source.attrs;
    for (const key in this.source.attrs) {
      if (newAttrs[key] === void 0) {
        this._removeAttribute(key, this.source.attrs[key]);
        delete this.source.attrs[key];
      } else {
        this._setAttribute(key, newAttrs[key], this.source.attrs[key]);
        this.source.attrs[key] = newAttrs[key];
      }
      delete newAttrs[key];
    }
    for (const key in newAttrs) {
      this._setAttribute(key, newAttrs[key], void 0);
      this.source.attrs[key] = newAttrs[key];
    }
  }
  _setAttribute(name, value, old) {
    if (name.indexOf("on") === 0) {
      let event = name.substring(2).toLowerCase();
      if (old !== void 0) {
        this.el.removeEventListener(event, old);
      }
      this.el.addEventListener(event, value);
      return;
    }
    if (value === true) {
      value = "";
    }
    this.el.setAttribute(name, value);
  }
  _removeAttribute(name, old) {
    if (name.indexOf("on") === 0) {
      let event = name.substring(2).toLowerCase();
      this.el.removeEventListener(event, old);
      return;
    }
    this.el.removeAttribute(name);
  }
  resolveRelativeNodes() {
    return [this.el, this.el];
  }
  resolveNextNodeOf(child) {
    return void 0;
  }
};
var Text = class extends Entity {
  constructor(value) {
    super();
    this.value = value;
  }
  onMount() {
    this.node = document.createTextNode(this.value);
    this.insertNode(this.node);
  }
  onUnmount() {
    this.el.removeChild(this.node);
    this.node = void 0;
  }
  onMorph(other) {
    this.value = other.value;
    this.node.textContent = this.value;
  }
  resolveRelativeNodes() {
    return [this.node, this.node];
  }
  resolveNextNodeOf(child) {
    return void 0;
  }
};
var HelloWorld = class extends Entity {
  onMount() {
    this.node = document.createTextNode("Hello World (" + Math.floor(Math.random() * 100) + ")");
    this.insertNode(this.node);
  }
  onUnmount() {
    this.el.removeChild(this.node);
    this.node = void 0;
  }
  onMorph(other) {
    this.node.textContent = "Hello World (" + Math.floor(Math.random() * 100) + ")";
  }
  resolveRelativeNodes() {
    return [this.node, this.node];
  }
  resolveNextNodeOf(child) {
    return void 0;
  }
};
var Component = class {
  constructor(source) {
    this.morphMode = 0;
    this.source = source;
  }
  morphType(mode) {
    this.morphMode = mode;
    return this;
  }
};
var Invoke = class extends Entity {
  constructor(component, props, slots) {
    super();
    this.states = {};
    this.changed = false;
    this.component = component;
    this.props = props;
    this.slots = slots;
  }
  onMount() {
    for (const slotKey in this.component.source.slots) {
      if (this.slots[slotKey] === void 0) {
        this.slots[slotKey] = () => this.component.source.slots[slotKey](this);
      }
    }
    this.states = this.component.source.states(this);
    this.content = this.component.source.render(this);
    this.content.mount(this, this.parentE, this.el);
  }
  onUnmount() {
    this.content.unmount();
  }
  reset() {
    this.states = this.component.source.states(this);
  }
  refresh() {
    const newRender = this.component.source.render(this);
    this.content.morph(newRender);
    this.changed = false;
  }
  onMorph(other) {
    if (this.component.morphMode == 0) {
      let changed = false;
      if (JSON.stringify(this.props) !== JSON.stringify(other.props)) {
        changed = true;
        this.props = other.props;
      }
      if (changed) {
        this.content.morph(other.component.source.render(this));
      }
    }
    if (this.component.morphMode == 1) {
      this.content.morph(other.component.source.render(this));
    }
  }
  getState(name) {
    return this.states[name];
  }
  setState(name, value) {
    this.states[name] = value;
    this.changed = true;
  }
  track(callback) {
    callback();
    if (this.changed) {
      this.refresh();
    }
  }
  getSlot(name = "") {
    return this.slots[name];
  }
  getProp(name) {
    return this.props[name];
  }
  getApiResult(name) {
    return null;
  }
  get(name) {
    if (this.states[name] !== void 0) {
      return this.states[name];
    }
    if (this.props[name] !== void 0) {
      return this.props[name];
    }
    if (this.slots[name] !== void 0) {
      return this.slots[name];
    }
    return void 0;
  }
  resolveRelativeNodes() {
    return this.content.resolveRelativeNodes();
  }
  resolveNextNodeOf(child) {
    return this.parent.resolveNextNodeOf(this);
  }
};
var If = class extends Entity {
  constructor(condition, source) {
    super();
    this.source = source;
    this.condition = condition;
  }
  onMount() {
    if (this.condition) {
      this.source.mount(this, this.parentE, this.el);
    }
  }
  onUnmount() {
    if (this.condition) {
      this.source.unmount();
    }
  }
  onMorph(other) {
    let newCond = other.condition;
    if (newCond != this.condition) {
      if (newCond) {
        this.source.mount(this, this.parentE, this.el);
      } else {
        this.source.onUnmount();
      }
      this.condition = newCond;
    }
  }
  resolveRelativeNodes() {
    if (this.condition) {
      return this.source.resolveRelativeNodes();
    } else {
      return [void 0, void 0];
    }
  }
  resolveNextNodeOf(child) {
    return this.parent.resolveNextNodeOf(this);
  }
};
window["StackWeb"] = {
  Entity,
  Root,
  Group,
  Dom,
  Text,
  HelloWorld,
  Component,
  Invoke,
  If
};
export {
  Component,
  Dom,
  Entity,
  Group,
  HelloWorld,
  If,
  Invoke,
  Root,
  Text
};
