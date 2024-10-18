import { getComponent } from '@symfony/ux-live-component';
import {Controller} from "@hotwired/stimulus";
import Papa from 'papaparse'

export default class extends Controller {
    static values = {
        "formName": String,
        "formId": String,
        "columnMap": Array,
    }

    static targets = [
        "add",
        "import",
        "importField",
    ];

    async initialize() {
        super.initialize();

        this.parentComponent = await getComponent(this.scope.element.closest("div[data-controller^=live]"));
    }

    importData() {
        const value = this.importFieldTarget.value.trim();
        const data = Papa.parse(value, {
            "delimiter": "\t",
        });

        if (data.data.length <= 0) {
            return;
        }

        const dataLength = data.data.length;
        const rowNumber = this.element.querySelectorAll("table > tbody > tr").length;
        let x = null;

        if (dataLength > rowNumber) {
            let rowsMissing = dataLength - rowNumber;
            for (let i = 0; i < rowsMissing; i++) {
                x = this.parentComponent.action("addCollectionItem", {
                    "name": this.formNameValue,
                });
            }
        }

        if (x) {
            x.then(() => this.fillInRows(data.data))
        } else {
            this.fillInRows(data.data);
        }
    }

    fillInRows(data) {
        data.map((row, index) => {
            let offset = this.columnMapValue.length - row.length;
            row.forEach((colValue, colIndex) => this.fillInColumn(colValue.trim(), colIndex, index, offset))
        });
    }

    fillInColumn(colValue, colIndex, rowIndex, offset) {
        let fieldId = this.getIdForColumn(colIndex + offset, rowIndex);
        let elm = document.getElementById(fieldId);

        switch (elm.nodeName) {
            case "INPUT":
                if (elm.type.toLowerCase() === "checkbox") {
                    colValue = colValue.toLowerCase();

                    if (["ok", "yes", "ja", "1", "on"].includes(colValue)) {
                        elm.value = 1;
                        elm.checked = true;
                    } else {
                        elm.value = 1;
                        elm.checked = false;
                    }
                } else {
                    elm.value = colValue;
                }

                break;
            case "SELECT":
                let options = Array.from(elm.options);
                let selectedOption = null;

                // Search by text
                selectedOption = options.find((option) => option.text === colValue);

                // Search for substring
                if (!selectedOption) {
                    selectedOption = options.find((option) => option.text.includes(colValue));
                }

                // Search for value
                if (!selectedOption) {
                    selectedOption = options.find((option) => option.value === colValue);
                }

                if (!selectedOption) {
                    selectedOption = options.find((option) => option.value.includes(colValue));
                }

                if (selectedOption) {
                    elm.value = selectedOption.value;
                } else {
                    elm.value = colValue;
                }

                break;
            case "TEXTAREA":
                elm.value = colValue;
                break;
        }

        elm.dispatchEvent(new Event("change", {"bubbles": true}));
    }

    rowExists(index) {
        let firstColumn = this.getNameForColumn(this.columnMapValue[0][0], index);

        if (this.parentComponent.element.querySelector(`[name^='${firstColumn}']`)) {
            return true;
        } else {
            return false;
        }
    }

    getNameForColumn(column, index) {
        return `${this.formNameValue}[${index}][${column}]`;
    }

    getIdForColumn(colIndex, index) {
        const columnMap = this.columnMapValue[colIndex];
        let columnName = null;

        if (columnMap[2].length > 0) {
            columnName = `${columnMap[2]}_${columnMap[0]}`;
        } else {
            columnName = columnMap[0];
        }

        return `${this.formIdValue}_${index}_${columnName}`;
    }
}
