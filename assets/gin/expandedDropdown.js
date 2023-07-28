
document.getElementsByClassName("dropdown");

function addHoverDropdown() {
    let dropdownElements = document.querySelectorAll(".dropdown");

    dropdownElements.forEach((e) => {
        e.addEventListener("mouseover", () => {
            e.classList.add("show");
            e.querySelector("a.dropdown-toggle").attributes["aria-expanded"] = "true";
            e.querySelector(".dropdown-menu").classList.add("show");
        })

        e.addEventListener("mouseout", () => {
            e.classList.remove("show");
            e.querySelector(".dropdown-menu").classList.remove("show");
            e.querySelector("a.dropdown-toggle").attributes["aria-expanded"] = "false";
        })
    });
}


//document.addEventListener("readystatechange", (e => addHoverDropdown()));
document.documentElement.addEventListener("turbo:load", (e) => addHoverDropdown());

