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

    async onSmilesChanged()
    {
        new Promise((resolve, reject) => {
            resolve(this.smilesTarget.querySelector("input").value)
        }).then(function(smiles) {
            let mol = window.RDKit.get_mol(smiles);
            console.log(mol);
        });

        //const mol = Molecule.fromSmiles(newSmiles);
    }
}