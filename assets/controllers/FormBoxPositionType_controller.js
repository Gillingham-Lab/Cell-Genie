import {Controller} from "@hotwired/stimulus";
import BoxDrawer from "../gin/Drawing/BoxDrawer";

export default class extends Controller {
    static values = {
        "boxName": String,
        "coordinateName": String,
        "apiEndpoint": String,
    }

    static targets = [
        "box",
        "coordinate",
        "drawing",
    ];

    initialize() {
        super.initialize();
    }

    connect() {
        let boxElement = this.boxTarget.querySelector(`[name^='${this.boxNameValue}']`);

        boxElement.addEventListener("change", this.onBoxChange.bind(this));
        this.updateBoxView(boxElement.value);
    }

    async onBoxChange() {
        this.updateBoxView();
    }

    async updateBoxView() {
        let boxElement = this.boxTarget.querySelector(`[name^='${this.boxNameValue}']`);
        let boxId = boxElement.value;

        let response = await fetch(this.apiEndpointValue.replaceAll("boxId", boxId), {
            method: "GET",
            headers: new Headers({
                "Content-Type": "application/json"
            })
        });

        const json = await response.json();

        this.drawBox(json["box"], json["map"]);
    }

    drawBox(box, boxMap) {
        const drawer = new BoxDrawer(
            this.drawingTarget,
            box,
            boxMap,
            function () {
                return this.coordinateTarget.querySelector(`[name^='${this.coordinateNameValue}']`).value;
            }.bind(this),
            function (coordinate) {
                this.coordinateTarget.querySelector(`[name^='${this.coordinateNameValue}']`).value = coordinate;
                this.coordinateTarget.querySelector(`[name^='${this.coordinateNameValue}']`).dispatchEvent(new Event("change"));
            }.bind(this),
        );

        drawer.draw();
    }
}