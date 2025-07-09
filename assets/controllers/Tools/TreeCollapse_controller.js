import { Controller } from "@hotwired/stimulus";
import { Collapse } from "bootstrap";

import Storage from "../../gin/Storage";

export default class extends Controller
{
    static values = {
        "id": String,
        "activePath": Boolean,
    }

    static targets = [
        "body",
        "icon",
    ]

    connect()
    {
        super.connect();

        this.storage = new Storage("GinTreeCollapse");

        this.body = new Collapse(this.bodyTarget, {
            toggle: false,
        })

        if (this.activePathValue === false) {
            // Only apply 'remember' if it is not the currently active path.
            this.restoreRememberedState();
        }
    }

    restoreRememberedState()
    {
        let rememberedState = this.storage.get(this.idValue);

        if (rememberedState === "shown" && !this.bodyTarget.classList.contains("show")) {
            // Show if hidden but remembered shown
            this.show();
        } else if (rememberedState === "collapsed" && this.bodyTarget.classList.contains("show")) {
            // Hide if shown but remembered hidden
            this.hide();
        }
    }

    show()
    {
        this.body.show();
        this.iconTarget.classList.remove("fa-plus-square");
        this.iconTarget.classList.add("fa-minus-square");
    }

    hide()
    {
        this.body.hide();
        this.iconTarget.classList.remove("fa-minus-square");
        this.iconTarget.classList.add("fa-plus-square");
    }

    toggle()
    {
        if (this.bodyTarget.classList.contains("show")) {
            this.hide();
            this.storage.put(this.idValue, "collapsed");
        } else {
            this.show();
            this.storage.put(this.idValue, "shown");
        }
    }
}