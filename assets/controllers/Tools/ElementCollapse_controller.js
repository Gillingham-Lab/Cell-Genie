import { Controller } from "@hotwired/stimulus";
import { Collapse } from "bootstrap";

import Storage from "../../gin/Storage";

export default class extends Controller
{
    static values = {
        "id": String,
    }

    static targets = [
        "body",
    ]

    connect()
    {
        super.connect();

        this.storage = new Storage("GinElementCollapse");

        this.body = new Collapse(this.bodyTarget, {
            toggle: false,
        })

        this.restoreRememberedState();
    }

    restoreRememberedState()
    {
        let rememberedState = this.storage.get(this.idValue);

        if (rememberedState === "shown" && !this.bodyTarget.classList.contains("show")) {
            // Show if hidden but remembered shown
            this.body.show();
        } else if (rememberedState === "collapsed" && this.bodyTarget.classList.contains("show")) {
            // Hide if shown but remembered hidden
            this.body.hide();
        }
    }

    toggle()
    {
        if (this.bodyTarget.classList.contains("show")) {
            this.body.hide();
            this.storage.put(this.idValue, "collapsed");
        } else {
            this.body.show();
            this.storage.put(this.idValue, "shown");
        }
    }
}