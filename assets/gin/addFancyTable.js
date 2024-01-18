const fancyTable = require('jquery.fancytable');

const addFancyTable = () => {
    document.querySelectorAll(".fancyTable").forEach((e) => {
        // Remove existing search bars
        e.querySelectorAll(".fancySearchRow").forEach((searchRow) => {
            searchRow.remove();
        })

        // Default sort column
        let sortColumn = 1;
        if (e.hasAttribute("data-ft-sort-column")) {
            sortColumn = this.getAttribute("data-ft-sort-column");
        }

        // Add fancy table
        $(e).fancyTable({
            sortColumn: sortColumn,
            pagination: true,
            perPage: 30,
            globalSearch: true,
            globalSearchExcludeColumns: [0],
        })
    })
}

document.addEventListener("turbo:load", (e => addFancyTable()));
