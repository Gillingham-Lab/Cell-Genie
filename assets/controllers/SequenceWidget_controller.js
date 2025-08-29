import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static values = {
        "sequence": String,
        "type": String,
    }

    static targets = [
        "content",
    ]

    initialize() {
        super.initialize();
    }

    connect()
    {
        this.draw();
    }

    draw()
    {
        console.log(this.typeValue);

        if (!this.hasContentTarget || !this.hasSequenceValue) {
            return;
        }

        const target = this.contentTarget;
        const sequence = this.sequenceValue;
        let content = [];
        let enumeration = [];

        let groupSize = 9;
        let nine = true;

        if (this.typeValue === "peptide") {
            groupSize = 10;
            nine = false;
        }

        console.log(this.typeValue, this.typeValue === "peptide", groupSize);

        for (let i = 0; i < sequence.length; i = i+groupSize) {
            let decet = sequence.substr(i, groupSize);
            content.push(`<span class="group ${nine?"nine":""}">${decet}</span>`);
            enumeration.push(`<span class="counter ${nine?"nine":""}">${i+groupSize}</span>`);
        }

        target.innerHTML = `
            <div class="sequence">
                ${content.join("")}
            </div>
            <div class="sequence-enumeration">
                ${enumeration.join("")}
            </div>
        `;
    }
}