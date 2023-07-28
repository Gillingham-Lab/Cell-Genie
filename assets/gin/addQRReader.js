import {Html5QrcodeScanner} from "@scanapp/html5-qrcode";
const $ = require("jquery");

const addQRReader = () => {
    /*let qrcodeReader = document.getElementById("qr-reader");
    let qrcodeResult = document.getElementById("qr-reader-results");
    let qrcodeToggle = document.getElementById("qr-reader-toggle");
    let html5Scanner = null;

    qrcodeToggle.addEventListener("click", (e) => {
        console.log(qrcodeReader);

        if (qrcodeReader.style.display === "none") {
            html5Scanner = new Html5QrcodeScanner(
                "qr-reader", {
                    fps: 10,
                    qrbox: 100,
                },
                false,
            )

            html5Scanner.render((decodedText, decodedResult) => {
                $(qrcodeReader).toggle()
                qrcodeResult[0].innerHTML = decodedText;

                html5Scanner.clear();

                console.log(decodedText);
            });
        } else {
            if (html5Scanner !== null) {
                html5Scanner.clear();
                html5Scanner = null;
            }
        }

        //qrcodeReader.toggle();
    });*/

    let qrcodeReader = $("#qr-reader");
    let qrcodeResult = $("#qr-reader-results")
    let html5Scanner = null;

    $("#qr-reader-toggle").on("click", function (e) {
        if (qrcodeReader.css("display") === "none") {
            html5Scanner = new Html5QrcodeScanner(
                "qr-reader", {
                    fps: 10,
                    qrbox: 100,
                }
            );

            html5Scanner.render(function (decodedText, decodedResult) {
                let redirect = document.getElementById("qr-reader-toggle").dataset.homepage;
                qrcodeReader.toggle();

                qrcodeResult[0].innerHTML = decodedText;
                html5Scanner.clear();

                // Now do something with the decoded text
                if (decodedText.startsWith("gin:///")) {
                    redirect += decodedText.substring(7);

                    window.location.href = redirect;
                } else {
                    decodedText = decodedText.replace("/", "")
                    window.location.href = redirect + "barcode/" + decodedText;
                }
            });
        } else {
            if (html5Scanner !== null) {
                html5Scanner.clear();
                html5Scanner = null;
            }
        }

        qrcodeReader.toggle();
    })
};

document.addEventListener("turbo:load", (e => addQRReader()));

/*
<script type="application/javascript">
    $(document).ready(function() {
        let qrcodeReader = $("#qr-reader");
        let qrcodeResult = $("#qr-reader-results")
        let html5Scanner = null;

        $("#qr-reader-toggle").on("click", function (e) {
            if (qrcodeReader.css("display") === "none") {
                html5Scanner = new Html5QrcodeScanner(
                    "qr-reader", {
                        fps: 10,
                        qrbox: 100,
                    }
                );

                html5Scanner.render(function (decodedText, decodedResult) {
                    let redirect = "{{ url("app_homepage") }}";
                    qrcodeReader.toggle();

                    qrcodeResult[0].innerHTML = decodedText;
                    html5Scanner.clear();

                    // Now do something with the decoded text
                    if (decodedText.startsWith("gin:///")) {
                        redirect += decodedText.substring(7);

                        window.location.href = redirect;
                    } else {
                        decodedText = decodedText.replace("/", "")
                        window.location.href = redirect + "barcode/" + decodedText;
                    }
                });
            } else {
                    if (html5Scanner !== null) {
                    html5Scanner.clear();
                    html5Scanner = null;
                }
            }

            qrcodeReader.toggle();
        })
    })
</script>
*/
