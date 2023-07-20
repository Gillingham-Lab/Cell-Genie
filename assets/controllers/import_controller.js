import { Controller } from '@hotwired/stimulus';
import * as Papa from 'papaparse'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        "data": Object,
    };

    static targets = [
        "data",
        "delimiter",
        "hasHeader",
        "importMeta",
        "importAssignment",
    ];

    connect() {

    }

    prepareImport(event) {
        console.log("Import starts ...");

        let data = Papa.parse(this.dataTarget.value);

        if (this.hasHeader() === false) {
            data.headers = [];

            for (let i = 0; i < data.data[0].length; i++) {
                data.headers.push(`Column #${i}`);
            }
        } else {
            data.headers = data.data[0];
        }

        data.headersIndex = {};
        for (let i = 0; i < data.headers.length; i++) {
            data.headersIndex[data.headers[i]] = i;
        }

        // Fill in option values
        this.importAssignmentTarget.querySelectorAll(".gin-import-column-select").forEach(function (select) {
            // First, only add "none" option
            select.innerHTML = '<option value="none" selected>None</option>';

            // Then, add the others
            for (const header of data.headers) {
                let option = document.createElement("option");
                option.value = header;
                option.innerHTML = header;

                select.appendChild(option);
            }
        });

        this.dataValue = data;
        this.displayMeta(data);
    }

    runImport(event) {
        // Get configuration
        const assignment = this.importAssignmentTarget;
        let dataQuery = [];
        let startAt = 0;
        if (this.hasHeader()) {
            startAt = 1;
        }
        let data = this.dataValue;

        // Do this for every data row
        for (let i = startAt; i < this.dataValue.data.length; i++) {
            let dataObject = {};
            let dataRow = this.dataValue.data[i];

            assignment.querySelectorAll(".gin-import-group").forEach(function (group) {
                dataObject[group.dataset.name] = {};

                group.querySelectorAll(".gin-import-column").forEach(function (column) {
                    let value = null;
                    let assignmentColumn = column.querySelector(".gin-import-column-select");
                    let staticColumnName = assignmentColumn.name.substr(assignmentColumn.name.search("-")+1);
                    let staticColumn = column.querySelector(`[name^='${staticColumnName}']`);

                    if (assignmentColumn.value === "none") {
                        // Value is empty, therefore, grab the value of the input field
                        if (staticColumn.value) {
                            value = staticColumn.value;
                        }
                    } else {
                        let columnIndex = data.headersIndex[assignmentColumn.value];
                        value = dataRow[columnIndex];
                    }

                    dataObject[group.dataset.name][column.dataset.name] = value;
                });
            });

            dataQuery.push(dataObject);
        }

        console.log(dataQuery);
    }

    hasHeader() {
        if (this.hasHasHeaderTarget && this.hasHeaderTarget.value === "yes") {
            return true;
        } else {
            return false;
        }
    }

    displayMeta(data) {
        if (this.hasImportMetaTarget) {
            let delimiter = JSON.stringify(data.meta.delimiter);
            let linebreak = JSON.stringify(data.meta.linebreak);
            let numberOfRows = data.data.length;
            let hasHeader = false;

            if (this.hasHasHeaderTarget && this.hasHeaderTarget.value === "yes") {
                numberOfRows = numberOfRows - 1;
                hasHeader = true;
            }

            this.importMetaTarget.innerHTML = `Data has been parsed. Found ${numberOfRows} rows and `
                + `${data.data[0].length} columns. Colum delimiter was ${delimiter}; row delimiter `
                + `was ${linebreak}.`;
        }
    }
}
