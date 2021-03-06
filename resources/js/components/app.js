class Card extends HTMLElement {
  constructor() {
    super();
    this.attachShadow({ mode: "open" });

    this.shadowRoot.innerHTML = `
        <style>
            .card {
                margin 1em;
                background: white;
                border-radius: 0.5em;
                box-shadow: 0 2px 8px rgba(0,0,0,0.26);
            }

            .card__title{
              font-weight: 500;
              font-size: 1.5rem;
              padding: 1em;
            }

            ::slotted(h2) {
                font-size: 1.5rem;
                font-weight: 500;
                margin: 0;
            }

            .card__body {
              padding: 2em;
            }
        </style>
        <div class="card">
            <header class="card__title">
              <slot name="title"></slot>
            </header>
            <section class="card__body">
              <slot></slot>
            </section>
        </div>
      `;
  }
}

export const card = customElements.define("ts-card", Card);
