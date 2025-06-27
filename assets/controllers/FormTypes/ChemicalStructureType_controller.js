import {Controller} from "@hotwired/stimulus";

export default class extends Controller {
    static targets = [
        "smiles",
        "molecularMass",
        "smilesViewer",
    ]

    connect()
    {
        super.connect();

        this.smilesTarget.querySelector("input").addEventListener("change", this.onSmilesChanged.bind(this));
    }

    onSmilesChanged()
    {
        const newSmiles = this.smilesTarget.querySelector("input").value;
        const mol = Molecule.fromSmiles(newSmiles);
    }
}