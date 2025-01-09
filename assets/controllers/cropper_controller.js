import { getComponent } from '@symfony/ux-live-component';
import {Controller} from '@hotwired/stimulus';
const bootstrap = require("bootstrap");
const cropper = require("cropperjs");

export default class extends Controller {
    static values = {
        name: String,
    };

    static targets = [
        "dropZone",
        "uploadField",
        "imageStorage",
        "croppedImageStorage",
        "cropperModal",
        "formField",
    ]

    #reader;
    #modal;
    #cropper;

    async initialize() {
        super.initialize();

        this.reader = new FileReader();
        this.reader.onload = (event) => this.onFileLoad(event.target.result);

        if (this.formFieldTarget.value) {
            this.displayCroppedImage(this.formFieldTarget.value);
        }

        this.modal = new bootstrap.Modal(this.cropperModalTarget, {
            keyboard: true,
        });
    }

    onDrop(event) {
        event.preventDefault();

        if (event.dataTransfer.items) {
            [...event.dataTransfer.items].forEach((item, i) => {
               if (item.kind === "file") {
                   const file = item.getAsFile();
                   this.processFile(file);
               }
            });
        } else {
            [...event.dataTransfer.files].forEach((file, i) => {
                this.processFile(file);
            })
        }
    }

    onFileUploadChange(event) {
        // Prevent the event from bubbling up. This stops the component from updating before we are dispatch our own.
        event.cancelBubble = true;

        if (event.target.files.length > 0) {
            this.processFile(event.target.files[0]);
        }
    }

    onClick(event) {
        this.uploadFieldTarget.click();

        event.preventDefault();
    }

    onDragOver(event) {
        event.preventDefault();
    }

    onFileLoad(result) {
        if (this.cropper) {
            this.cropper.destroy();
        }

        if (result.length === 0) {
            return;
        }

        this.imageStorageTarget.src = result;
        this.imageStorageTarget.classList.remove("d-none");
        this.modal.show();

        this.cropper = new cropper(this.imageStorageTarget, {
            autoCropArea: 1,
            viewMode: 2,
        })
    }

    onEditAgain(event) {
        this.onFileLoad(this.croppedImageStorageTarget.src);
    }

    processFile(file) {
        this.reader.readAsDataURL(file);
    }

    onCropperSubmit(event) {
        event.preventDefault();

        let dataUrl = this.cropper.getCroppedCanvas().toDataURL();
        this.displayCroppedImage(dataUrl);
        this.updateFormField(dataUrl);

        this.modal.hide();
    }

    displayCroppedImage(dataUrl) {
        this.croppedImageStorageTarget.src = dataUrl;
        this.croppedImageStorageTarget.classList.remove("d-none");
        this.dropZoneTarget.childNodes.forEach((e) => e.nodeType === 3 ? e.remove() : null);
    }

    onRemove(event) {
        this.croppedImageStorageTarget.src = undefined;
        this.croppedImageStorageTarget.classList.add("d-none");
        this.updateFormField("");
    }

    updateFormField(dataUrl) {
        this.formFieldTarget.value = dataUrl;
        this.formFieldTarget.dispatchEvent(new Event("change", {bubbles: true}))
    }
}
