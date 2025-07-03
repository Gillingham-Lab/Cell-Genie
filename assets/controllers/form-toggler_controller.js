import { Controller } from '@hotwired/stimulus';
import {Collapse} from "bootstrap";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        viewPath: String,
        formPath: String,
    }

    static targets = [
        "frame",
        "form",
        "view",
        "button",
    ];

    connect() {
        this.frame = null;

        this.formTarget.addEventListener('turbo:submit-end', (event) => {
            if (event.detail.success) {
                // force-reload log list
                console.log(this.viewPathValue);
                console.log(this.viewTarget);
                this.viewTarget.getElementsByTagName("turbo-frame")[0].reload();
                this.toggle()
            }
        });
    }

    toggle() {
        this.doToggle();
    }

    doToggle(empty_form=true) {
        if (!this.frame) {
            this.frames = this.frameTargets.map((elm) => new Collapse(elm))
        }
        this.frames.map((elm) => {
            elm.toggle();
        })

        let button = this.buttonTarget

        if (button.classList.contains("btn-primary")) {
            button.classList.remove("btn-primary");
            button.classList.add("btn-danger");
            button.innerHTML = "<span class='fa fa-fw fa-times'></span>";

            if (empty_form) {
                let formTurboFrame = this.formTarget.getElementsByTagName("turbo-frame")[0];
                if (!formTurboFrame.src || formTurboFrame.src !== this.formPathValue) {
                    formTurboFrame.src = this.formPathValue;
                }
            }
        } else {
            button.classList.remove("btn-danger");
            button.classList.add("btn-primary");
            button.innerHTML = "<span class='fa fa-fw fa-plus'></span>";
        }
    }

    edit(event) {
        this.doToggle(false);
        let formTurboFrame = this.formTarget.getElementsByTagName("turbo-frame")[0];
        formTurboFrame.src = event.params.path;
    }

    trash(event) {
        fetch(event.params.path).then(() => this.viewTarget.getElementsByTagName("turbo-frame")[0].reload());
    }
}