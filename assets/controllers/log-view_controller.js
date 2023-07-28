import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        "contentUrl": String,
    }

    static targets = [

    ]

    connect() {
        this.element.innerHTML = "<div class='spinner-border' role='status'><span class='visually-hidden'>Loading ...</span></div>"

        if (this.hasContentUrlValue) {
            fetch(this.contentUrlValue).then((response) => {
                if (response.status === 200) {
                    response.text().then((text) => {
                        this.element.innerHTML = text;
                    })
                } else if (response.status === 400) {
                    response.text().then((text) => {
                        this.element.innerHTML = "<div class='alert alert-danger' role='alert'>Logs have not been found.</div>";
                    })
                } else {
                    response.text().then((text) => {
                        this.element.innerHTML = "<div class='alert alert-danger' role='alert'>Loading the logs failed.</div>";
                    })
                }
            })
        } else {
            this.element.textContent = "This log view controller has not a url attached."
        }
    }
}
