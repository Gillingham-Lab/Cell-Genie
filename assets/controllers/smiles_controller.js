import { Controller } from '@hotwired/stimulus';
import {parse, Drawer} from "smiles-drawer";

export default class extends Controller {
    static values = {
        "id": String,
        "smiles": String,
        "padding": {type: Number, default: 20.0},
    };

    static targets = [
        "canvas",
        "smiles",
    ];

    initialize() {
        super.initialize();
    }

    draw(event = null) {
        let clientWidth = this.canvasTarget.clientWidth;

        let canvasOptions = {
            width: clientWidth,
            height: clientWidth * 0.67,
            bondThickness: 1.0,
            bondLength: 10,
            shortBondLength: 0.6,
            bondSpacing: 0.25 * 10,
            fontSizeLarge: 6,
            fontSizeSmall: 4,
            padding: this.paddingValue,
        }

        let smilesDrawer = new Drawer(canvasOptions);

        parse(
            this.smilesValue,
            (tree) => {
                smilesDrawer.draw(tree, this.canvasTarget, "light", false);
            },
            (error) => {
                console.log("Error while drawing smiles", error);
            }
        );

        this.smilesTarget.innerHTML = this.smilesValue;

        return true;
    }

    connect() {
        this.draw()
    }
}