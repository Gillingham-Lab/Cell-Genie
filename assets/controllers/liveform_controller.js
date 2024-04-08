import { getComponent } from '@symfony/ux-live-component';
import {Controller} from '@hotwired/stimulus';
import formHelpers from './../gin/formHelpers.js';

export default class extends Controller {
    async initialize() {
        this.component = await getComponent(this.element);

        this.component.on('render:finished', (component) => {
            formHelpers(component.element);
        });
    }
}