import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        "id": String,
        "api": String,
    };

    static targets = [
        "ingredientTable",
        "totalVolume",
        "concentrationFactor",
    ];

    initialize() {
        super.initialize();
    }

    recalculate(event = null) {
        console.log(this.totalVolumeTarget);

        let valueFields = [].slice.call(this.ingredientTableTarget.querySelectorAll("._genie_recipe_ingredient_amount, ._genie_recipe_ingredient_amount_volume"));
        valueFields.map(function (element) {
            element.innerHTML = '<span class="spinner-border text-primary" role="status">\n' +
                '  <span class="visually-hidden">Loading...</span>\n' +
                '</span>'
        })

        fetch(`${this.apiValue}${this.idValue}`, {
            method: "POST",
            body: JSON.stringify({
                "volume": this.totalVolumeTarget.value,
                "concentrationFactor": this.concentrationFactorTarget.value,
            })
        }).then(function (response) {
            response.json().then(function (data) {
                console.log(data);

                data.ingredients.map(function (ingredient) {
                    let row = document.getElementById("ingredient_" + ingredient.id);
                    let mass_column = row.querySelector("._genie_recipe_ingredient_amount");
                    let volume_column = row.querySelector("._genie_recipe_ingredient_amount_volume");

                    mass_column.innerHTML = ingredient.quantity_formatted;
                    if (ingredient.volume) {
                        volume_column.innerHTML = ingredient.volume_formatted;
                    } else {
                        volume_column.innerHTML = "-";
                    }
                })
            });
        })

        return true;
    }

    connect() {
        this.recalculate()
    }
}