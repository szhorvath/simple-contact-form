class HTMLTypedInputElement extends HTMLInputElement {
  constructor() {
    const self = super();

    console.log(self.value);
  }

  get value() {
    return this.getAttribute("value");
  }

  set value(val) {
    if (val instanceof Date) {
      this.value = val.toISOString().slice(0, this.type === "date" ? 10 : -1);
      return;
    }
    if (typeof val === "boolean") {
      this.checked = val;
      return;
    }
    this.value = `${val}`;
  }
}

export const input = customElements.define("ts-input", HTMLTypedInputElement, {
  extends: "input",
});
