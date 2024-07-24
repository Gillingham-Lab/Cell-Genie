import {Tooltip} from "bootstrap";

const initializeTooltip = (elm) => {
    if ("tooltipReference" in elm.dataset) {
        new Tooltip(elm, {
            "html": true,
            "title": document.getElementById(elm.dataset.tooltipReference).innerHTML,
            "sanitize": false,
            "customClass": "white-tooltip",
            "delay": {
                "show": 100,
                "hide": 1500,
            }
        });
    } else {
        new Tooltip(elm, {
            "html": true,
        });
    }
};

const addTooltips = (target=null) => {
    if (!target) {
        target = document;
    }

    target.querySelectorAll(".tooltip").forEach((elm) => {
        elm.remove();
    });

    target.querySelectorAll("[data-toggle='tooltip']").forEach((elm) => {
        initializeTooltip(elm);
    });

    target.querySelectorAll("[data-bs-toggle='tooltip']").forEach((elm) => {
        initializeTooltip(elm);
    })
}

document.addEventListener("turbo:frame-load", (e => addTooltips()));
document.addEventListener("turbo:load", (e => addTooltips()));
document.addEventListener("turbo:render", (e => addTooltips()));
document.addEventListener("turbo:morph", (e => addTooltips()));

export default addTooltips