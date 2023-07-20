import {Controller} from '@hotwired/stimulus';
import * as Papa from 'papaparse'
import {Collapse} from "bootstrap";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        "data": Object,
        "postUrl": String,
    };

    static targets = [
        "data",
        "delimiter",
        "hasHeader",
        "importMeta",
        "importAssignment",
        "errorMessages",
        "validateOnly",
        "ignoreErrors",
    ];

    connect() {
    }

    prepareImport(event) {
        console.log("Import starts ...");

        let data = Papa.parse(this.dataTarget.value);

        data.headers = [];
        if (this.hasHeader() === false) {

            for (let i = 0; i < data.data[0].length; i++) {
                data.headers.push(`Column #${i}`);
            }
        } else {
            let i = 0;
            for (let columnHead of data.data[0]) {
                data.headers.push(`${i}: ${columnHead}`)
                i = i+1;
            }
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

        (new Collapse(this.importAssignmentTarget)).show()
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

        let columnCount = data.headers.length;

        // Do this for every data row
        for (let i = startAt; i < this.dataValue.data.length; i++) {
            let dataObject = {};
            let dataRow = this.dataValue.data[i]

            // Ignore imperfect rows.
            if (dataRow.length < columnCount) {
                continue;
            }

            assignment.querySelectorAll(".gin-import-group").forEach(function (group) {
                dataObject[group.dataset.name] = {};

                group.querySelectorAll(".gin-import-column").forEach(function (column) {
                    let value = null;
                    let assignmentColumn = column.querySelector(".gin-import-column-select");
                    let staticColumnName = assignmentColumn.name.substr(assignmentColumn.name.search("-") + 1);
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

        let importResultMethod = this.showImportResult.bind(this);
        let errorMessagesTarget = this.errorMessagesTarget;

        fetch(this.postUrlValue, {
            "method": "POST",
            "headers": {
                "Accept": "application/json",
                "Content-type": "application/json",
            },
            "body": JSON.stringify({
                "data": dataQuery,
                "options": {
                    "validateOnly": this.validateOnlyTarget.value === "yes",
                    "ignoreErrors": this.ignoreErrorsTarget.value === "yes",
                },
            })
        }).then(function (response) {
            response.json().then(function (answer) {
                importResultMethod(answer);
            });
        });

        if (!this.errorMessagesTarget.classList.contains("show")) {
            let collapsibleErrorMessageTarget = new Collapse(this.errorMessagesTarget);
            collapsibleErrorMessageTarget.show()
        }
    }

    showImportResult(answer) {
        this.errorMessagesTarget.classList.remove("border-danger", "text-danger", "border-warning", "text-warning", "border-success", "text-success");

        if (answer.errors && answer.numRowsCreated === 0) {
            this.errorMessagesTarget.classList.add("border", "border-danger", "text-danger")

            let errors = this.makeErrorList(answer.errors)

            this.errorMessagesTarget.innerHTML = `<p>Out of ${answer.numRows}, there have been ${answer.errors.length} lines with an error.</p><ul>${errors}</ul>`;
        } else if (answer.errors && answer.numRowsCreated > 0) {
            this.errorMessagesTarget.classList.add("border", "border-warning", "text-warning")

            this.errorMessagesTarget.innerHTML = `Out of ${answer.numRows}, ${answer.numRowsCreated} have been added to the database without errors.`;
        } else {
            if (answer.numRowsCreated === 0) {
                this.errorMessagesTarget.innerHTML = `Valid! Everything is valid, you can deactivate the validate only option now if you want to make`;
                this.errorMessagesTarget.classList.add("border", "border-success", "text-success")
            } else {
                (new Collapse(this.importAssignmentTarget)).hide()

                this.errorMessagesTarget.innerHTML = `Successfully imported <strong>${answer.numRowsCreated}</strong> rows.`;
            }
        }
    }

    makeErrorList(allErrors) {
        let errors = "";

        let i = 0;
        for (let error of allErrors) {
            for (let suberror of error) {
                i = i+1;

                errors += `<li>Row ${suberror.row}, field ${suberror.path}: ${suberror.message}</li>`

                if (i > 10) {
                    errors += "<li>... and more</li>"
                    break;
                }
            }

            if (i > 10) {
                break;
            }
        }

        return errors;
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
