import {Tooltip} from "bootstrap";

const addTooltips = () => {
    document.querySelectorAll("[data-toggle='tooltip']").forEach((elm) => {
        new Tooltip(elm, {
            "html": true,
        });
    });

    document.querySelectorAll("[data-bs-toggle='tooltip']").forEach((elm) => {
        new Tooltip(elm, {
            "html": true,
        });
    })
}

document.addEventListener("turbo:load", (e => addTooltips()));