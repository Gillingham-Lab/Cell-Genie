import TomSelect from "tom-select";
const $ = require("jquery");
const bootstrap = require("bootstrap");

const formHelpers = (e = null) => {
    console.log("Run form helpers", e);

    let target = document;
    if (e) {
        target = e.target;
    }

    // Bubble up error indicators
    let accordions = target.querySelectorAll("div.accordion-item");
    accordions.forEach((elm) => {
        let elm_search = elm.querySelectorAll(".is-invalid");

        if (elm_search.length > 0) {
            elm.classList.add("border-warning");
        }
    });

    // Fancy
    let fancySelections = target.querySelectorAll("select.gin-fancy-select");
    fancySelections.forEach((elm) => {
        elm.data
        let tomElm = new TomSelect(elm, {
            plugins: {
                dropdown_input: true,
            },
            maxOptions: 1000,
            sortField: [{field: 'text'}, {field: '$order'}, {field: '$score'}],
            create: !!elm.dataset["allowAdd"],
            allowEmptyOption: elm.attributes["allowEmpty"] && elm.attributes["allowEmpty"].value === "true",
            render: {
                optgroup_header: function (data, escape) {
                    return '<div class="optgroup-header"><strong>' + escape(data.label) + '</strong></div>';
                },
                option: function (data, escape) {
                    if (data.optgroup === undefined) {
                        return '<div>' + escape(data.text) + '</div>';
                    } else {
                        return '<div class="ps-3">' + escape(data.text) + '</div>';
                    }
                },
            }
        });
    });

    // Remembering some collapse states
    $(target).ready(function() {
        let shownOnRequest;

        shownOnRequest = localStorage.getItem("shownOnRequest");

        if (!shownOnRequest) {
            shownOnRequest = {};
        } else {
            shownOnRequest = JSON.parse(shownOnRequest);
        }

        $('.collapse').on("shown.bs.collapse", function(e) {
            let id = $(this).attr('id');
            shownOnRequest[id] = 1;
            localStorage.setItem("shownOnRequest", JSON.stringify(shownOnRequest));

            return true;
        }).on("hidden.bs.collapse", function(e) {
            let id = $(this).attr('id');

            shownOnRequest[id] = 0;
            localStorage.setItem("shownOnRequest", JSON.stringify(shownOnRequest));

            return true;
        });

        $("a[data-bs-toggle^='tab']").on("shown.bs.tab", function(e) {
            let id = $(this).attr('id');
            shownOnRequest[id] = 1;
            localStorage.setItem("shownOnRequest", JSON.stringify(shownOnRequest));

            return true;
        }).on("hidden.bs.tab", function(e) {
            let id = $(this).attr('id');

            shownOnRequest[id] = 0;
            localStorage.setItem("shownOnRequest", JSON.stringify(shownOnRequest));

            return true;
        })

        let collapsible = null;
        for (let id in shownOnRequest) {
            if (shownOnRequest[id] === 1) {
                collapsible = $("#" + id);
                if (collapsible.length > 0 && collapsible.hasClass("navbar-collapse") === false && collapsible.css("display") === "none") {
                    let bsCollapse = new bootstrap.Collapse(collapsible).show();

                } else if (collapsible.length > 0 && collapsible.hasClass("nav-link")) {
                    collapsible.tab("show");
                }
            }
        }

        $("input.gin-datetime-autofill[type^=datetime-local]").each(function (i, e) {
            let now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            now.setMilliseconds(null)
            now.setSeconds(null)

            e.valueAsDate = now;
            //e.value = e.value.slice(0,16);
            //console.log(e.value.slice(0, 16));
        })

        $(function() {
            $('.list-group').on('shown.bs.collapse', function(e) {
                let id = this.id;

                if (e.target === this) {
                    $(`#anchor-for-${id} > .collapse-icon`, this.parentElement)
                        .toggleClass('fa-plus-square')
                        .toggleClass('fa-minus-square');
                }
            }).on('hidden.bs.collapse', function(e) {
                let id = this.id;

                if (e.target === this) {
                    $(`#anchor-for-${id} > .collapse-icon`, this.parentElement)
                        .toggleClass('fa-plus-square')
                        .toggleClass('fa-minus-square');
                }
            });
        });
    });
}

document.addEventListener("turbo:load", (e => formHelpers()));
document.addEventListener("turbo:frame-load", (e => formHelpers(e)));


/* Original script
<script type="application/javascript" defer>
            $(document).ready(function() {
                let shownOnRequest;

                shownOnRequest = localStorage.getItem("shownOnRequest");

                if (!shownOnRequest) {
                    shownOnRequest = {};
                } else {
                    shownOnRequest = JSON.parse(shownOnRequest);
                }

                $('.collapse').on("shown.bs.collapse", function(e) {
                    let id = $(this).attr('id');
                    shownOnRequest[id] = 1;
                    localStorage.setItem("shownOnRequest", JSON.stringify(shownOnRequest));

                    return true;
                }).on("hidden.bs.collapse", function(e) {
                    let id = $(this).attr('id');

                    shownOnRequest[id] = 0;
                    localStorage.setItem("shownOnRequest", JSON.stringify(shownOnRequest));

                    return true;
                });

                $("a[data-bs-toggle^='tab']").on("shown.bs.tab", function(e) {
                    let id = $(this).attr('id');
                    shownOnRequest[id] = 1;
                    localStorage.setItem("shownOnRequest", JSON.stringify(shownOnRequest));

                    return true;
                }).on("hidden.bs.tab", function(e) {
                    let id = $(this).attr('id');

                    shownOnRequest[id] = 0;
                    localStorage.setItem("shownOnRequest", JSON.stringify(shownOnRequest));

                    return true;
                })

                let collapsible = null;
                for (let id in shownOnRequest) {
                    if (shownOnRequest[id] === 1) {
                        collapsible = $("#" + id);
                        if (collapsible.length > 0 && collapsible.hasClass("navbar-collapse") === false && collapsible.css("display") === "none") {
                            let bsCollapse = new bootstrap.Collapse(collapsible).show();

                        } else if (collapsible.length > 0 && collapsible.hasClass("nav-link")) {
                            collapsible.tab("show");
                        }
                    }
                }

            $("input.gin-datetime-autofill[type^=datetime-local]").each(function (i, e) {
                now = new Date();
                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                now.setMilliseconds(null)
                now.setSeconds(null)

                e.valueAsDate = now;
                //e.value = e.value.slice(0,16);
                //console.log(e.value.slice(0, 16));
            })

            $(function() {
                $('.list-group').on('shown.bs.collapse', function(e) {
                    let id = this.id;

                    if (e.target === this) {
                        $(`#anchor-for-${id} > .collapse-icon`, this.parentElement)
                            .toggleClass('fa-plus-square')
                            .toggleClass('fa-minus-square');
                    }
                }).on('hidden.bs.collapse', function(e) {
                    let id = this.id;

                    if (e.target === this) {
                        $(`#anchor-for-${id} > .collapse-icon`, this.parentElement)
                            .toggleClass('fa-plus-square')
                            .toggleClass('fa-minus-square');
                    }
                });
            });
        });
</script>

<script type="application/javascript" defer>
            $(document).ready(function() {
                $("select.gin-fancy-select").each(function (e) {
                    new TomSelect(this, {
                        plugins: {
                            dropdown_input: true,
                        },
                        maxOptions: 1000,
                        sortField:[{field: 'text'}, {field: '$order'}, {field: '$score'}],
                        create: !!this.attributes["data-allow-add"],
                        allowEmptyOption: this.attributes["data-allow-empty"] && this.attributes["data-allow-empty"].value === "true",
                        render: {
                            optgroup_header: function(data, escape) {
                                return '<div class="optgroup-header"><strong>' + escape(data.label) + '</strong></div>';
                            },
                            option: function(data, escape) {
                                if (data.optgroup === undefined) {
                                    return '<div>' + escape(data.text) + '</div>';
                                } else {
                                    return '<div class="ps-3">' + escape(data.text) + '</div>';
                                }
                            },
                        }
                    })
                })
            })
        </script>
 */