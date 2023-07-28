import ClipboardJS from "clipboard";

const addClipboard = () => {
    let clipboard = new ClipboardJS('.btn-clipboard');
};

document.addEventListener("turbo:load", (e => addClipboard()));

/* Original code

        <script type="application/javascript" defer>
            new ClipboardJS('.btn-clipboard');
        </script>

 */