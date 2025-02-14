import {Controller} from "@hotwired/stimulus";
import Plotly from "plotly.js-dist"
import "d3";
import React from 'react';
import {createRoot} from 'react-dom/client';

export default class extends Controller {
    static values = {
        model: Object,
        fits: Array,
        showWarnings: Boolean,
        showErrors: Boolean,
        oneTraceOnly: Boolean,
        width: {
            type: Number,
            default: 0,
        },
        height: {
            type: Number,
            default: 0,
        }
    }

    static targets = [
        "plot",
        "warnings",
        "errors",
    ]

    initialize() {
    }

    connect() {
        this.plotly = Plotly.newPlot(this.plotTarget, this.getData(), this.getLayout(), this.getConfig());

        this.parseMessages();
    }

    parseMessages() {
        this.warnings = createRoot(this.warningsTarget);
        this.errors = createRoot(this.errorsTarget);

        let warnings = [];
        let errors = [];
        let i = 0;

        for (const fit of this.fitsValue) {
            console.log(fit);
            if ("warnings" in fit.fit.result) {
                for (const warning of fit.fit.result.warnings) {
                    warnings.push(<p key={i}><strong>{fit.condition}:</strong> {warning}</p>);
                    i++;
                }
            }

            if ("errors" in fit.fit.result) {
                for (const error of fit.fit.result.errors) {
                    errors.push(<p key={i}><strong>{fit.condition}:</strong> {error}</p>);
                    i++;
                }
            }
        }

        if (this.showWarningsValue && warnings.length > 0) {
            this.warnings.render(<div className="alert border-warning-subtle bg-warning-subtle text-black">{warnings}</div>)
        }

        if (this.showErrorsValue && errors.length > 0) {
            this.errors.render(<div className="alert border-danger-subtle bg-danger-subtle text-black">{errors}</div>);
        }
    }

    disconnect() {
        Plotly.purge(this.plotTarget);

        this.warnings.render(<></>)
        this.errors.render(<></>)
    }

    getLayout() {
        let values = this.fitsValue.filter((e) => "evaluation" in e.fit.result);

        let scale = values.length >= 1 ? values[0].fit.result.evaluation.spacing : "linear";
        let min = Math.min(... values.map(e => e.fit.result.evaluation.min));
        let max = Math.max(... values.map(e => e.fit.result.evaluation.max));

        return {
            width: this.widthValue > 0 ? this.widthValue : null,
            height: this.heightValue > 0 ? this.heightValue : null,
            xaxis: {
                linewidth: 1,
                type: scale,
                min: min,
                max: max,
                title: {
                    text: this.modelValue.configuration.x,
                },
            },
            yaxis: {
                linewidth: 1,
                title: {
                    text: this.modelValue.configuration.y,
                    standoff: 20,
                },
            },
            margin: {
                l: 50,
                r: 20,
                t: 20,
                b: 30,
            },
            legend: {
                orientation: "h",
                yanchor: "top",
                xanchor: "center",
                y: -0.2,
                x: 0.5,
            },
            showlegend: !this.oneTraceOnlyValue,
        };
    }

    getData() {
        let data = [];
        let i = 0;
        let colorscheme = d3.schemeCategory10;

        for (const fit of this.fitsValue) {
            let color = colorscheme[i % 10];

            if (!("fit" in fit.fit.result)) {
                continue;
            }

            let xerr = fit.fit.result.fit.x.concat(fit.fit.result.fit.x.toReversed());
            let yerr = d3.zip(fit.fit.result.fit.y, fit.fit.result.fit.yerr);
            let yerr_u = yerr.map(e => e[0] + e[1]);
            let yerr_l = yerr.map(e => e[0] - e[1]);

            data.push({
                x: xerr,
                y: yerr_u.concat(yerr_l.toReversed()),
                type: 'scatter',
                name: `Fit ${fit.condition}`,
                mode: "lines",
                fill: "tozerox",
                fillcolor: color + "33",
                line: {
                    color: "transparent",
                }
            });

            data.push({
                x: fit.fit.result.fit.x,
                y: fit.fit.result.fit.y,
                type: 'scatter',
                name: `Fit ${fit.condition}`,
                mode: "lines",
                line: {
                    color: color,
                }
            });

            data.push({
                x: fit.fit.result.x,
                y: fit.fit.result.y,
                type: 'scatter',
                name: fit.condition,
                mode: "markers",
                marker: {
                    color: color,
                }
            });

            if ("referenceFit" in fit && fit.referenceFit !== null) {
                console.log(fit.referenceFit);

                if ("x" in fit.referenceFit && "y" in fit.referenceFit) {
                    data.push({
                        x: fit.referenceFit.x,
                        y: fit.referenceFit.y,
                        type: 'scatter',
                        name: `Reference ${fit.condition}`,
                        mode: "markers",
                        marker: {
                            line: {
                                width: 0.5,
                                color: "black",
                            },
                            color: color,
                        }
                    });
                }

                if ("fit" in fit.referenceFit) {
                    data.push({
                        x: fit.referenceFit.fit.x,
                        y: fit.referenceFit.fit.y,
                        type: 'scatter',
                        name: `Reference Fit ${fit.condition}`,
                        mode: "lines",
                        line: {
                            color: "black",
                        }
                    });
                }
            }

            i++;
        }

        return data;
    }

    getConfig() {
        return {
            toImageButtonOptions: {
                format: "svg",
            },
        };
    }
}