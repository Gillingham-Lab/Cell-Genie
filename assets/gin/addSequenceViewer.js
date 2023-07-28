import Sequence from "sequence-viewer";
const $ = require("jquery");

const addSequenceViewer = () => {
    $(document).ready(function () {
        let viewers = $(".gin-sequenceViewer")

        viewers.each(function (e) {
            let sequence = new Sequence(this.innerText);

            sequence.render("#" + this.id, {
                'showLineNumbers': true,
                'wrapAminoAcids': true,
                'charsPerLine': 100,
                'toolbar': true,
                'search': true,
                'title': this.hasAttribute("data-gin-sequence-title") ? this.getAttribute("data-gin-sequence-title") : "Sequence Viewer",
                'header' : {
                    display:true,
                    searchInTitle :true,
                    unit: "Char",
                    showCpl: false,
                    badgeWithUnit : false
                }
            })
        })
    });
};

document.addEventListener("turbo:load", (e => addSequenceViewer()));

/* Original code

        <script type="application/javascript" defer>
            new ClipboardJS('.btn-clipboard');
        </script>

 */