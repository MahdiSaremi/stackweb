var __defProp = Object.defineProperty;
var __export = (target, all) => {
  for (var name in all)
    __defProp(target, name, { get: all[name], enumerable: true });
};

// js/php-functions.ts
var php_functions_exports = {};
__export(php_functions_exports, {
  gettype: () => gettype,
  is_bool: () => is_bool,
  is_double: () => is_double,
  is_float: () => is_float,
  is_null: () => is_null,
  is_object: () => is_object,
  is_string: () => is_string
});
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

// js/php.ts
var PHPUtils = class {
  static opAdd(left, right) {
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
        return +value;
      default:
        return 1;
    }
  }
  static toString(value) {
    let type = typeof value;
    switch (type) {
      case "bigint":
      case "number":
        return "" + value;
      case "boolean":
        return value ? "1" : "";
      case "undefined":
        return "";
      case "object":
        return value === null ? "" : "object";
      default:
        return "object";
    }
  }
};
var PHP = {
  functions: php_functions_exports
};
window.PHP = PHP;

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
  }
  onMount() {
    this.el = document.createElement(this.source.name);
    this.insertNode(this.el);
    this.source.slot.mount(this, this, this.el);
  }
  onUnmount() {
    this.el.remove();
    this.el = void 0;
  }
  onMorph(other) {
    this.source.slot.morph(other.source.slot);
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
