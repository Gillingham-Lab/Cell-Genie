import {Controller} from "@hotwired/stimulus";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        "inputId": String,
        "inputFullName": String,
        "url": String,
    };

    static targets = [
        "generation",
        "error",
    ];

    inputElement;

    connect() {
        let inputId = this.inputIdValue;
        this.inputElement = this.element.querySelector(`#${inputId}`);
    }

    generate(event) {
        let enumeration_type = event.params.enumerationType;
        fetch(this.urlValue, {
            method: "POST",
            body: JSON.stringify({
                "enumeration_type": enumeration_type,
            }),
            headers: {
                "Content-Type": "application/json",
            },
        }).then(this.onGenerationResponse.bind(this));
    }

    onGenerationResponse(response) {
        if (response.ok) {
            response.json().then(this.onGenerationSuccess.bind(this));
        } else {
            response.json().then(this.onGenerationFailure.bind(this));
        }
    }

    onGenerationSuccess(response) {
        this.generationTarget.classList.add("btn-outline-success");
        this.generationTarget.classList.remove("btn-outline-primary");
        this.generationTarget.classList.remove("btn-outline-danger");

        this.inputElement.value = response.next_number;
        this.inputElement.dispatchEvent(new Event("change"));
        this.errorTarget.innerHTML = "";
    }

    onGenerationFailure(response) {
        this.generationTarget.classList.add("btn-outline-danger");
        this.generationTarget.classList.remove("btn-outline-primary");
        this.generationTarget.classList.remove("btn-outline-success");

        const errors = response.errors;
        if (errors) {
            let messages = [];
            errors.forEach(error => messages.push(`<p>${error.message}</p>`));
            this.errorTarget.innerHTML = messages;
        }
    }
}