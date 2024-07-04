import {parse, Drawer, SvgDrawer} from 'smiles-drawer';

const applyDrawing = (elements, smilesDrawer) => {
    elements.forEach((element) => {
        parse(element.dataset.smiles, (tree) => {
            console.log(tree);
            smilesDrawer.draw(tree, element, "light", false);
        }, (error) => {
            console.log(error);
        })
    });
}

const smilesDrawer = () => {
    let canvasOptions = {
        width: 100,
        height: 100,
        bondThickness: 1.0,
        bondLength: 10,
        shortBondLength: 0.6,
        bondSpacing: 0.25 * 10,
        fontSizeLarge: 6,
        fontSizeSmall: 4,
        padding: 20.0,
    }

    let smilesDrawer = new Drawer(canvasOptions);

    let smallCanvases = document.querySelectorAll("[data-smiles-type='small']")
    let largeCanvases = document.querySelectorAll("[data-smiles-type='large']")
    applyDrawing(smallCanvases, smilesDrawer);
    applyDrawing(largeCanvases, smilesDrawer);
}

//document.addEventListener("readystatechange", (e => smilesDrawer()));
document.documentElement.addEventListener("turbo:load", (e) => smilesDrawer());
