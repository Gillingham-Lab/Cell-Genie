import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static targets = [
        "collectionContainer"
    ];

    static values = {
        "prototype": String,
        "index": Number,
    }

    addCollectionElement(event)
    {
        const item = document.createElement("div");
        item.innerHTML = this.prototypeValue.replace(/__name__/g, this.indexValue);
        this.collectionContainerTarget.appendChild(item);
        this.indexValue++;
    }

    removeCollectionElement(event)
    {
        const element = document.getElementById(event.params["id"]);
        element.remove();
    }
}