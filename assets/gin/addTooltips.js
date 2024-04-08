import {Tooltip} from "bootstrap";

const addTooltips = (target=null) => {
    if (!target) {
        target = document;
    }

    target.querySelectorAll("[data-toggle='tooltip']").forEach((elm) => {
        new Tooltip(elm, {
            "html": true,
        });
    });

    target.querySelectorAll("[data-bs-toggle='tooltip']").forEach((elm) => {
        new Tooltip(elm, {
            "html": true,
        });
    })
}

document.addEventListener("turbo:frame-load", (e => addTooltips()));
document.addEventListener("turbo:load", (e => addTooltips()));
document.addEventListener("turbo:render", (e => addTooltips()));

export default addTooltips