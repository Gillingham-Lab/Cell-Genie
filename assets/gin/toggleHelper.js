const $ = require("jquery");
const bootstrap = require("bootstrap");

const toggleHelper = () => {
    // Register icon change
    let listGroups = [].slice.call(document.querySelectorAll(".list-group"));

    listGroups.map(function (listGroup) {
        listGroup.addEventListener("show.bs.collapse", function (e) {
            let id = this.id;

            if (e.target === listGroup) {
                let element = listGroup.parentElement.querySelector(`#anchor-for-${id} .collapse-icon`);
                element.classList.add("fa-minus-square");
                element.classList.remove("fa-plus-square");
            }
        })

        listGroup.addEventListener("hide.bs.collapse", function (e) {
            let id = this.id;

            if (e.target === listGroup) {
                let element = listGroup.parentElement.querySelector(`#anchor-for-${id} .collapse-icon`);
                element.classList.remove("fa-minus-square");
                element.classList.add("fa-plus-square")
            }
        })
    });

    //
    // Remember collapsed states
    //
    let shownOnRequest = localStorage.getItem("shownOnRequest");
    if (!shownOnRequest) {
        shownOnRequest = {};
    } else {
        shownOnRequest = JSON.parse(shownOnRequest);
    }

    // Add events to remember state of collapsibles
    [].slice.call(document.querySelectorAll(".collapse")).map(function (collapseItem) {
        collapseItem.addEventListener("show.bs.collapse", function (e) {
            let id = collapseItem.id;
            shownOnRequest[id] = 1;
            localStorage.setItem("shownOnRequest", JSON.stringify(shownOnRequest));
        });
        collapseItem.addEventListener("hide.bs.collapse", function (e) {
            let id = collapseItem.id;
            shownOnRequest[id] = 0;
            localStorage.setItem("shownOnRequest", JSON.stringify(shownOnRequest));
        });
    });

    [].slice.call(document.querySelectorAll("a[data-bs-toggle^='tab']")).map(function (collapseItem) {
        collapseItem.addEventListener("show.bs.collapse", function (e) {
            let id = collapseItem.id;
            shownOnRequest[id] = 1;
            localStorage.setItem("shownOnRequest", JSON.stringify(shownOnRequest));
        });
        collapseItem.addEventListener("hide.bs.collapse", function (e) {
            let id = collapseItem.id;
            shownOnRequest[id] = 0;
            localStorage.setItem("shownOnRequest", JSON.stringify(shownOnRequest));
        });
    });

    // Revert state of collapsibles
    let collapsible = null;
    for (let id in shownOnRequest) {
        collapsible = document.getElementById(`${id}`);

        if (!collapsible) {
            continue;
        }

        if (shownOnRequest[id] === 1) {
            if (collapsible.classList.contains("collapse") && !collapsible.classList.contains("show")) {
                new bootstrap.Collapse(collapsible).show();
            }
        } else if (shownOnRequest[id] === 0) {
            if (collapsible.classList.contains("collapse") && collapsible.classList.contains("show")) {
                new bootstrap.Collapse(collapsible).hide();
            }
        }
    }
}

document.addEventListener("turbo:load", (e => toggleHelper()));
document.addEventListener("turbo:frame-load", (e => toggleHelper(e)));

export default toggleHelper