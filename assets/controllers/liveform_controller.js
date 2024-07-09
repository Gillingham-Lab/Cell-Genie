import { getComponent } from '@symfony/ux-live-component';
import {Controller} from '@hotwired/stimulus';
import formHelpers from './../gin/formHelpers.js';
const bootstrap = require("bootstrap");

export default class extends Controller {
    async initialize() {
        this.component = await getComponent(this.element);

        let c = this.component;

        this.component.on('render:finished', (component) => {
            formHelpers(component.element);

            let active_item = localStorage.getItem("_tmp_active_tab");
            if (active_item) {
                let active_elm = document.getElementById(active_item);

                if (active_elm) {
                    new bootstrap.Tab(active_elm).show();
                }

                localStorage.removeItem("_tmp_active_tab");
            }
        });

        this.component.on('render:started', () => {
            let nav_items = c.element.querySelectorAll("#form-tab-navigation .nav-link");

            nav_items.forEach((elm) => {
                if (elm.classList.contains("active")) {
                    localStorage.setItem("_tmp_active_tab", elm.id);
                }
            })
        });
    }
}