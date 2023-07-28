import { Controller } from '@hotwired/stimulus';
import {Collapse} from "bootstrap";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        "contentUrl": String,
    }

    static targets = [
        "logView",
        "logForm",
        "closeButton",
        "addButton",
    ]

    connect() {
        if (this.hasContentUrlValue) {
            fetch(this.contentUrlValue).then((response) => {
                if (response.status === 200) {
                    response.text().then((text) => {
                        this.logFormTarget.innerHTML = text;
                    })
                } else if (response.status === 400) {
                    response.text().then((text) => {
                        this.logFormTarget.innerHTML = "<div class='alert alert-danger' role='alert'>Logs have not been found.</div>";
                    })
                } else {
                    response.text().then((text) => {
                        this.logFormTarget.innerHTML = "<div class='alert alert-danger' role='alert'>Loading the logs failed.</div>";
                    })
                }
            })
        } else {
            this.logFormTarget.textContent = "This log view controller has not a url attached."
        }

        this.logFormTarget.addEventListener("shown", (e) => {console.log(e)});
    }

    toggleForm() {
        // Hide LogView, show form
        if (this.logViewTarget.classList.contains("show")) {
            this.addButtonTarget.classList.remove("btn-primary");
            this.addButtonTarget.classList.add("btn-danger");
            this.addButtonTarget.innerHTML = "<span class='fa fa-fw fa-times'></span>"
        } else {
            this.addButtonTarget.classList.remove("btn-danger");
            this.addButtonTarget.classList.add("btn-primary");
            this.addButtonTarget.innerHTML = "<span class='fa fa-fw fa-plus'></span>"
        }

        (new Collapse(this.logViewTarget)).toggle();
        (new Collapse(this.logFormTarget)).toggle();
    }
}
