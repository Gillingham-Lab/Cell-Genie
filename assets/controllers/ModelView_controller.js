import {Controller} from "@hotwired/stimulus";
import Plotly from "plotly.js-dist"
import "d3";

export default class extends Controller {
    static values = {
        model: Object,
        fits: Array,
    }

    static targets = [
        "plot",
    ]

    initialize() {
    }

    connect() {
        this.plotly = Plotly.newPlot(this.plotTarget, this.getData(), this.getLayout(), this.getConfig());
    }

    disconnect() {
        this.plotly.destroy();
    }

    getLayout() {
        let scale = this.fitsValue.length > 1 ? this.fitsValue[0].fit.result.evaluation.spacing : "linear";
        let min = Math.min(... this.fitsValue.map(e => e.fit.result.evaluation.min));
        let max = Math.max(... this.fitsValue.map(e => e.fit.result.evaluation.max));

        return {
            xaxis: {
                type: scale,
                min: min,
                max: max,
                title: {
                    text: this.modelValue.configuration.x,
                },
            },
            yaxis: {
                title: {
                    text: this.modelValue.configuration.y,
                },
            },
            margin: {
                l: 50,
                r: 20,
                t: 20,
                b: 20,
            },
            legend: {
                orientation: "h",
                yanchor: "top",
                xanchor: "center",
                y: -0.2,
                x: 0.5,
            },
        };
    }

    getData() {
        let data = [];
        let i = 0;
        let colorscheme = d3.schemeCategory10;

        for (const fit of this.fitsValue) {
            let color = colorscheme[i % 10];

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