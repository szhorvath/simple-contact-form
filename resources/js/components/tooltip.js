class Tooltip extends HTMLElement {
  constructor() {
    super();

    const html = `
        <div></div>
      `;
  }

  connectedCallback() {
    const tooltipIcon = document.createElement("span");
    tooltipIcon.textContent = " (?)";
    this.appendChild(tooltipIcon);

    fetch("https://jsonplaceholder.typicode.com/posts/2")
      .then((response) => response.json())
      .then((data) => console.log(data));
  }
}

export const tooltip = customElements.define("sh-tooltip", Tooltip);
