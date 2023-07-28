const fancyTable = require('jquery.fancytable');

const addFancyTable = () => {
    $(".fancyTable").each(function (e) {
        let sortColumn = 1;

        if (this.hasAttribute("data-ft-sort-column")) {
            sortColumn = this.getAttribute("data-ft-sort-column");
        }

        $(this).fancyTable({
            sortColumn: sortColumn,
            pagination: true,
            perPage: 30,
            globalSearch: true,
            globalSearchExcludeColumns: [0],
        })
    })
}

document.addEventListener("turbo:load", (e => addFancyTable()));
