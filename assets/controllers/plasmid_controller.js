import { Controller } from '@hotwired/stimulus';

import * as d3 from "d3";

export default class extends Controller {
    static values = {
        sequence: String,
        sequenceLength: Number,
        features: Array,
    }

    static targets = ["plasmidMap", "plasmidFeatureDetails"];

    initialize() {
        super.initialize();

        this.permanentFeature = null;
    }

    angleToCoordinates(radius, angle) {
        return [
            radius * Math.cos((angle-90)/360*2*Math.PI),
            radius * Math.sin((angle-90)/360*2*Math.PI),
        ]
    }

    shiftCoordinates(coordinates, dx, dy) {
        return [
            coordinates[0] + dx,
            coordinates[1] + dy,
        ];
    }

    makeCircularPath(width, height, radius, fromAngle, toAngle) {
        let origin = this.angleToCoordinates(radius, fromAngle)
        let midPoint = this.angleToCoordinates(radius, (toAngle+fromAngle)/2)
        let target = this.angleToCoordinates(radius, toAngle)

        origin = this.shiftCoordinates(origin, width/2, height/2);
        midPoint = this.shiftCoordinates(midPoint, width/2, height/2);
        target = this.shiftCoordinates(target, width/2, height/2);

        return `M ${origin[0]} ${origin[1]} A ${radius} ${radius} 0 0 1 ${midPoint[0]} ${midPoint[1]} A ${radius} ${radius} 0 0 1 ${target[0]} ${target[1]}`;
    }

    getFeaturePath(width, height, radius, fromAngle, toAngle, radiusWidth=10, forwardDirection=true) {
        let innerRadius = radius-radiusWidth/2;
        let outerRadius = radius+radiusWidth/2;

        if (!forwardDirection) {
            let _ = fromAngle;
            fromAngle = toAngle - 360;
            toAngle = _;
        }

        let innerOrigin = this.angleToCoordinates(innerRadius, fromAngle)
        let innerMidPoint = this.angleToCoordinates(innerRadius, (toAngle+fromAngle)/2)
        let innerTarget = this.angleToCoordinates(innerRadius, toAngle)
        let outerOrigin = this.angleToCoordinates(outerRadius, fromAngle)
        let outerMidPoint = this.angleToCoordinates(outerRadius, (toAngle+fromAngle)/2)
        let outerTarget = this.angleToCoordinates(outerRadius, toAngle)

        innerOrigin = this.shiftCoordinates(innerOrigin, width/2, height/2);
        innerMidPoint = this.shiftCoordinates(innerMidPoint, width/2, height/2);
        innerTarget = this.shiftCoordinates(innerTarget, width/2, height/2);
        outerOrigin = this.shiftCoordinates(outerOrigin, width/2, height/2);
        outerMidPoint = this.shiftCoordinates(outerMidPoint, width/2, height/2);
        outerTarget = this.shiftCoordinates(outerTarget, width/2, height/2);

        return `M ${innerOrigin[0]} ${innerOrigin[1]} `
            + `A ${innerRadius} ${innerRadius} 0 0 1 ${innerMidPoint[0]} ${innerMidPoint[1]} A ${innerRadius} ${innerRadius} 0 0 1 ${innerTarget[0]} ${innerTarget[1]} `
            + `L ${outerTarget[0]} ${outerTarget[1]}`
            + `A ${outerRadius} ${outerRadius} 0 0 0 ${outerMidPoint[0]} ${outerMidPoint[1]} A ${outerRadius} ${outerRadius} 0 0 0 ${outerOrigin[0]} ${outerOrigin[1]} `
            + `Z`;
    }

    getFeatureColorForType(type) {
        switch (type) {
            case "CDS":
                return "#0099BB";
            case "promotor":
                return "#99BB00";
            case "primer":
            case "primer_bind":
                return "#BB9900";
            default:
                return "#999999";
        }
    }

    drawFeature(feature, svg, totalWidth, totalHeight, radius) {
        let featureStart = feature["start"]-1;
        let featureEnd = feature["end"];
        let normalDirection = true;

        // The start can be larger than the end if the feature crosses the 1-coordinate
        if (featureStart > featureEnd) {
            featureStart = feature["end"];
            featureEnd = feature["start"]-1;
            normalDirection = false;
        }

        let angleStart = (featureStart) / this.sequenceLengthValue * 360;
        let angleEnd = (featureEnd) / this.sequenceLengthValue * 360;

        console.log(featureStart, featureEnd, angleStart, angleEnd);

        if (Math.round(angleStart) === 0 && Math.round(angleEnd) === 360) {
            return;
        }

        let featureType = feature["type"].toLowerCase();
        let plasmidFeature = svg.append("g");

        let level = -1;

        if (["cds", "promoter", "rep_origin", "repeat_region", "polya_signal"].includes(featureType)) {
            level = 0;
        } else if (["primer", "primer_bind", "misc_feature", "protein_bind"].includes(featureType)) {
            level = 1;
        }

        plasmidFeature.append("path")
            //.attr("d", this.makeCircularPath(totalWidth, totalHeight, radius-5, angleStart, angleEnd))
            .attr("id", "plasmid-feature-element-" + feature["id"])
            .attr("d", this.getFeaturePath(totalWidth, totalHeight, radius + level*20, angleStart, angleEnd, 10, normalDirection))
            .attr("stroke-width", 1)
            .attr("stroke", "black")
            .attr("fill", (feature["color"] && feature["color"] !== "#000000") ? feature["color"] : this.getFeatureColorForType(feature["type"]))
            .on("mouseover", this.showFeature.bind(this, feature))
            .on("mouseout", this.hideFeature.bind(this))
            .on("click", this.showFeaturePermanently.bind(this, feature))
        ;

        let levelShift = level === 0 ? -20 : +20;

        plasmidFeature
            //.append("defs")
            .append("path")
            .attr("id", "plasmid-feature-label-path-" + feature["id"])
            .attr("d", this.makeCircularPath(totalWidth, totalHeight, radius + level*20 + levelShift, angleStart, angleEnd))
            .attr("stroke", "none")
            .attr("fill", "none")
        ;

        plasmidFeature.append("text")
            .attr("text-anchor", "middle")
            .attr("font-size", "10px")
            .append("textPath")
            .attr("startOffset", "50%")
            .attr("href", "#plasmid-feature-label-path-" + feature["id"])
            .text(feature["label"])
        ;
    }

    showFeaturePermanently(feature) {
        let oldFeature = this.permanentFeature;
        let featureElement = document.getElementById("plasmid-feature-element-" + feature["id"]);

        if (oldFeature === null || feature["id"] !== oldFeature["id"]) {
            this.permanentFeature = feature;
            this.showFeature(feature);

            featureElement.setAttribute("stroke-width", 2);

            if (oldFeature !== null) {
                let oldFeatureElement = document.getElementById("plasmid-feature-element-" + oldFeature["id"])

                oldFeatureElement.setAttribute("stroke-width", 1);
            }
        } else {
            // Toggle.html.twig state
            this.permanentFeature = null;
            featureElement.setAttribute("stroke-width", 1);
            this.plasmidFeatureDetailsTarget.innerText = null;
        }
    }

    hideFeature() {
        if (this.permanentFeature !== null) {
            this.showFeature(this.permanentFeature);
        }
    }

    showFeature(feature) {
        let featurePart = this.plasmidFeatureDetailsTarget;

        const header = document.createElement("h3");
        header.innerText = feature["label"];
        const type = document.createElement("h4");
        type.innerText = feature["type"];
        type.classList.add("text-muted");

        const infos = document.createElement("p");
        infos.innerText = `From ${feature['start']} to ${feature['end']} (${feature["end"]-feature["start"]+1} bp)`;

        const sequence = document.createElement("p");
        sequence.classList.add("font-monospace");
        sequence.classList.add("small");

        let sequenceSlice = this.sequenceValue.substring(feature["start"]-1, feature["end"]).toUpperCase();
        let parsedSequence = "";

        if (feature["complement"] === true) {
            for (let i=sequenceSlice.length; i > 0; i--) {
                let letter = sequenceSlice.substring(i, i+1);

                switch (letter) {
                    case "A": parsedSequence += "T"; break;
                    case "T": parsedSequence += "A"; break;
                    case "G": parsedSequence += "C"; break;
                    case "C": parsedSequence += "G"; break;
                    default: parsedSequence += letter; break;
                }

                if ((sequenceSlice.length-i)%10 === 9) {
                    parsedSequence += " ";
                }
            }
        } else {
            for (let i=0; i < sequenceSlice.length; i++) {
                parsedSequence += sequenceSlice.substring(i, i+1);

                if (i%10 === 9) {
                    parsedSequence += " ";
                }
            }
        }

        sequence.innerText = parsedSequence;

        const annotations = document.createElement("dl");

        for (const [key, value] of Object.entries(feature.annotations)) {
            let dt = document.createElement("dt");
            let dd = document.createElement("dd");

            dt.innerText = key;
            dd.innerText = value;

            annotations.appendChild(dt);
            annotations.appendChild(dd);
        }

        featurePart.textContent = "";
        featurePart.appendChild(header)
        featurePart.appendChild(type)
        featurePart.appendChild(infos)
        featurePart.appendChild(annotations)
        featurePart.appendChild(sequence)
    }

    connect() {
        super.connect();

        let totalWidth = this.element.clientWidth;
        if (this.element.offsetParent === null) {
            totalWidth = 400;
        }
        let totalHeight = 0;
        let margin = 100;
        let width = 0;
        let height = 0;

        if (totalWidth < 400) {
            width = totalWidth - 2*margin;
            height = totalWidth - 2*margin;
            totalHeight = totalWidth;
        } else {
            width = 400 - 2*margin;
            height = 400 - 2*margin;
            totalWidth = 400;
            totalHeight = 400;
        }

        this.plasmidMapTarget.innerHTML = "";

        let svg = d3.select(this.plasmidMapTarget)
            .append("svg")
            .attr("width", width + 2*margin)
            .attr("height", height + 2*margin)
            .style("margin", "0 auto")
            .append("g")
        ;

        let plasmidOutline = svg.append("path")
            .attr("d", this.makeCircularPath(totalWidth, totalHeight, width/2, 0, 360))
            .attr("stroke-width", 1)
            .attr("stroke", "black")
            .attr("fill", "none")
        ;

        this.featuresValue.forEach(
            function (svg, totalWidth, totalHeight, width, currentValue) {
                this.drawFeature(currentValue, svg, totalWidth, totalHeight, width/2)
            }.bind(this, svg, totalWidth, totalHeight, width)
        )

        let basepairText = svg.append("text")
            .attr("x", totalWidth/2)
            .attr("y", totalHeight/2)
            .attr("text-anchor", "middle")
            .attr("text-height", 14);

        basepairText.append("tspan")
            .attr("font-weight", "bold")
            .text(`${this.sequenceLengthValue} bp`)
    }
}
