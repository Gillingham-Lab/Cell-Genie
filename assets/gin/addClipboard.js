import ClipboardJS from "clipboard";

const addClipboard = () => {
    let clipboard = new ClipboardJS('.btn-clipboard');
};

document.addEventListener("turbo:load", (e => addClipboard()));

export default addClipboard;

