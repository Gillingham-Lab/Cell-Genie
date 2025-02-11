import {Controller} from "@hotwired/stimulus";
import TomSelect from "tom-select";

export default class extends Controller {
    initialize() {
    }

    cleanupTomSelect() {
        const tsWrappers = this.element.querySelectorAll(".ts-wrapper");
        tsWrappers.forEach(tsWrapper => {
            tsWrapper.remove();
        });
    }

    connect() {
        this.cleanupTomSelect();

        const selectElement = this.element.querySelector("select.gin-fancy-select-2");

        if (selectElement) {
            this.createFancyChoice(selectElement);
        }

        super.connect();
    }

    disconnect() {
        if (this.fancySelect) {
            this.fancySelect.destroy();
        }

        if (this.observer) {
            this.observer.disconnect();
        }

        this.cleanupTomSelect();

        super.disconnect();
    }

    createFancyChoice(element) {
        this.fancySelect = new TomSelect(element, {
            plugins: {
                dropdown_input: true,
            },
            maxOptions: 1000,
            sortField: [{field:'$order'},{field:'$score'}],
            create: !!element.dataset["allowAdd"],
            allowEmptyOption: (element.attributes["allowEmpty"] && element.attributes["allowEmpty"].value === "true") || (!!element.dataset["allowEmpty"]),
            render: {
                optgroup_header: function (data, escape) {
                    let label = escape(data.label);
                    if (label.length === 0) {
                        label.length = "&nbsp;";
                    }

                    return '<div class="optgroup-header"><strong>' + escape(data.label) + '</strong></div>';
                },
                option: function (data, escape) {
                    let text = escape(data.text);
                    if (text.length === 0) {
                        text = "&nbsp;";
                    }

                    if (data.optgroup === undefined) {
                        return '<div>' + text + '</div>';
                    } else {
                        return '<div class="ps-3">' + text + '</div>';
                    }
                },
            }
        });
    }
}