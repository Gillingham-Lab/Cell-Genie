import {Controller} from '@hotwired/stimulus';
import * as d3 from "d3";

export default class extends Controller {
    static values = {
        boxMap: Object,
        gridSize: {type: Number, default: 40},
        xShift: {type: Number, default: 40},
        yShift: {type: Number, default: 20},
        substanceTarget: String,
        cellTarget: String,
        currentAliquot: {type: Object, default: {}},
    }

    static targets = ["boxMap", "boxMapContent"];

    initialize() {
        super.initialize();

        this.boxMapTarget.innerHTML = "";
        this.svgContainer = null;
        this.permanentWell = null;
    }

    connect() {
        this.drawBoxGrid(this.boxMapTarget, this.boxMapValue.rows, this.boxMapValue.cols);
        this.fillBox(this.boxMapValue);

        if (this.currentAliquotValue) {
            this.onBoxEntryClick({"object": this.currentAliquotValue}, null);
        }
    }

    rowCoordinateToLetter(coordinate) {
        let ordA = "A".charCodeAt(0);

        // Convert to "0"-based
        coordinate = coordinate - 1;

        let string = "";

        while (coordinate >= 0) {
            string = String.fromCharCode(coordinate % 26 + ordA) + string;
            coordinate = Math.floor(coordinate / 26) - 1;
        }

        return string;
    }

    drawBoxGrid(target, rows, cols) {
        let grid_size = this.gridSizeValue;
        let x_shift = this.xShiftValue;
        let y_shift = this.yShiftValue;
        let grid_color = "grey";

        let width = grid_size*cols;
        let height = grid_size*rows;

        this.svgContainer = d3.select(target)
            .append("svg")
            .attr("viewBox", `0 0 ${width+x_shift*2} ${height+x_shift*2}`)
            .attr("preserveAspectRatio", "xMidYMid meet")
        ;

        let grid = this.svgContainer.append("g");

        grid.append("rect")
            .attr("width", width)
            .attr("height", height)
            .attr("x", x_shift)
            .attr("y", y_shift)
            .attr("stroke", grid_color)
            .attr("fill", "none")
        ;

        // Horizontal lines
        for (let i = 1; i <= rows; i++) {
            if (i < rows) {
                grid.append("path")
                    .attr("d", `M ${x_shift} ${grid_size*i+y_shift} h ${width}`)
                    .attr("stroke", grid_color)
                    .attr("stroke-width", 1)
                ;
            }

            grid.append("text")
                .attr("x", x_shift - 5)
                .attr("y", grid_size*(i-1)+y_shift+grid_size/2)
                .attr("text-anchor", "end")
                .attr("dominant-baseline", "middle")
                .append("tspan")
                .text(`${this.rowCoordinateToLetter(i)}`)
        }

        // Vertical lines
        for (let i = 1; i <= cols; i++) {
            if (i < cols) {
                grid.append("path")
                    .attr("d", `M ${grid_size*i+x_shift} ${y_shift}  v ${height}`)
                    .attr("stroke", grid_color)
                    .attr("stroke-width", 1)
                ;
            }

            grid.append("text")
                .attr("x", grid_size*(i-1)+x_shift+grid_size/2)
                .attr("y", y_shift - 5)
                .attr("text-anchor", "middle")
                .append("tspan")
                .text(`${i}`)
        }
    }

    fillBox(boxMap) {
        boxMap.map.forEach(this.drawEntry.bind(this));
    }

    drawEntry(e) {
        if (e.object === null) {
            return;
        }

        let icon = "?";
        let id = this.getIdFromObject(e.object);
        if (id === null) {
            id = Math.random().toString(16);
        }

        if (e.object && e.object.substance) {
            let typeParts = e.object.substance.type.split("\\");
            let type = typeParts[typeParts.length - 1];

            switch (type) {
                case "Plasmid":
                    icon = "\ue90b";
                    break;
                case "Chemical":
                    icon = "\ue907";
                    break;
                case "Antibody":
                    icon = "\ue902";
                    break;
                case "Oligo":
                    icon = "\ue900";
                    break;
                case "Protein":
                    icon = "\ue90e";
                    break;
            }
        } else if (e.object && e.object.cell) {
            icon = "\ue90a";
        }

        let grid_size = this.gridSizeValue;
        let x_shift = this.xShiftValue;
        let y_shift = this.yShiftValue;

        let startTime;
        let endTime;

        let borderColor = "black";

        if (this.currentAliquotValue && e.object.cell && this.currentAliquotValue.cell && this.currentAliquotValue.cell.id !== e.object.cell.id) {
            borderColor = "grey";
        }

        if (e.doublyOccupied) {
            borderColor = "red";
        }

        let strokeWidth = 0;
        let strokeColor = "grey";

        let mycoplasmaColor = "#DDDDDD";
        if (e.object.mycoplasmaResult === "positive") {
            mycoplasmaColor = "#ff8e8e";
        } else if (e.object.mycoplasmaResult === "negative") {
            mycoplasmaColor = "#caffb6";
        }

        // Overwrite background color if its the current aliquot
        let backgroundColor = "white";
        if (e.object.cell && this.currentAliquotValue && this.currentAliquotValue.cell && this.currentAliquotValue.id === e.object.id) {
            backgroundColor = "#b6e8ff";
            strokeWidth = 1;
            strokeColor = "black";
        }

        this.svgContainer
            .append("rect")
            .attr("fill", backgroundColor)
            .attr("x", grid_size*(e.col-1)+x_shift)
            .attr("y", grid_size*(e.row-1)+y_shift)
            .attr("width", grid_size)
            .attr("height", grid_size)
            .attr("stroke", strokeColor)
            .attr("stroke-width", strokeWidth)
        ;

        this.svgContainer
            .append("circle")
            .attr("data-substance", id)
            .attr("cx", grid_size*(e.col-1)+x_shift+grid_size/2)
            .attr("cy", grid_size*(e.row-1)+y_shift+grid_size/2)
            .attr("r", grid_size*0.9/2)
            .attr("stroke", borderColor)
            .attr("fill", (
                (e.object.cell && this.currentAliquotValue && this.currentAliquotValue.cell)
                    ? (
                        this.currentAliquotValue.cell.id === e.object.cell.id
                            ? e.object.vialColor
                            : "white"
                    ) : (
                        e.object.vialColor
                            ? e.object.vialColor
                            : "white"
                    )
                ))
            .on("mouseover", this.onBoxEntryMouseOver.bind(this, e))
            .on("mouseout", this.onBoxEntryMouseOut.bind(this))
            .on("click", this.onBoxEntryClick.bind(this, e))
            .on("dblclick", this.onBoxEntryDoubleClick.bind(this, e))
            .on("touchstart", function() { startTime = new Date(); })
            .on("touchend", function() {
                endTime = new Date();

                if ((endTime - startTime) > 500) {
                     this.onBoxEntryDoubleClick(e, null);
                } else {
                    this.onBoxEntryClick(e, null);
                }
            }.bind(this))
        ;

        this.svgContainer
            .append("text")
            .attr("x", grid_size*(e.col-1)+x_shift+grid_size/2)
            .attr("y", grid_size*(e.row-1)+y_shift+grid_size/2+10)
            .attr("text-anchor", "middle")
            .attr("font-family", "icomoon")
            .attr("font-size", 25)
            .attr("fill", borderColor)
            .append("tspan")
            .text(icon)
            .on("mouseover", this.onBoxEntryMouseOver.bind(this, e))
            .on("mouseout", this.onBoxEntryMouseOut.bind(this))
            .on("click", this.onBoxEntryClick.bind(this, e))
            .on("dblclick", this.onBoxEntryDoubleClick.bind(this, e))
            .on("touchstart", function() { startTime = new Date(); })
            .on("touchend", function() {
                endTime = new Date();

                if ((endTime - startTime) > 200) {
                    this.onBoxEntryDoubleClick(e, null);
                } else {
                    this.onBoxEntryClick(e, null);
                }
            }.bind(this))
        ;

        this.svgContainer
            .append("circle")
            .attr("cx", grid_size*(e.col-1)+x_shift+grid_size/8+grid_size/16)
            .attr("cy", grid_size*(e.row-1)+y_shift+grid_size-(grid_size/8+grid_size/16))
            .attr("r", grid_size/8)
            .attr("fill", mycoplasmaColor)
            .attr("stroke", "black")
            .attr("stroke-width", 0.5)
    }

    getIdFromObject(object) {
        if (object.substance) {
            return object.substance.ulid;
        } else if (object.cell) {
            return `cell-${object.cell.id}-aliquot-${object.number}`;
        } else {
            return null;
        }
    }

    relocateToOject(object) {
        if (!object) {
            return false;
        }

        if (object.substance) {
            window.location = this.substanceTargetValue.replace("placeholder-substance-id", object.substance.ulid);
        } else if (object.cell) {
            window.location = this.cellTargetValue.replace("placeholder-cell-numer", object.cell.number).replace("placeholder-aliquot-id", object.number);
        }
    }

    onBoxEntryDoubleClick(e, event) {
        this.relocateToOject(e.object)
    }

    onBoxEntryClick(e, event) {
        if (event && event.ctrlKey) {
            this.relocateToOject(e.object)
        } else {
            let oldFeature = this.permanentWell;

            this.showWellContent(e);

            // Turn off old feature
            if (oldFeature) {
                let id = this.getIdFromObject(oldFeature.object);

                if (id) {
                    let wellElements = document.querySelectorAll(`[data-substance="${id}"]`);
                    wellElements.forEach((e) => e.setAttribute("stroke-width", 1));
                }

                this.permanentWell = null;
            }

            // Only turn on if the new feature is different from the old feature
            if (oldFeature === null ||
                //(oldFeature && oldFeature.object && oldFeature.object.substance && e.object.substance && oldFeature.object.substance.ulid !== e.object.substance.ulid)
                oldFeature !== e
            ) {
                let id = this.getIdFromObject(e.object);

                if (id) {
                    let wellElements = document.querySelectorAll(`[data-substance="${id}"]`);
                    wellElements.forEach((e) => e.setAttribute("stroke-width", 2));
                }

                this.permanentWell = e;
            }
        }

        return true;
    }

    onBoxEntryMouseOver(e, event) {
        this.showWellContent(e);
    }

    onBoxEntryMouseOut(event) {
        let wellTarget = this.boxMapContentTarget;
        wellTarget.textContent = "";

        if (this.permanentWell) {
            this.showWellContent(this.permanentWell);
        }
    }

    showWellContent(e) {
        let wellTarget = this.boxMapContentTarget;

        if (e.object && e.object.substance) {
            this.showWellContentSubstance(wellTarget, e);
        } else if (e.object && e.object.cell) {
            this.showWellContentCell(wellTarget, e);
        }
    }

    showWellContentSubstance(target, e) {
        const substance = e.object.substance;
        const lot = e.object.lot;

        const header = document.createElement("h3");
        const subHeader = document.createElement("h4");
        const annotationElement = document.createElement("table");

        if (substance.number) {
            header.innerText = `${substance.number}.${lot.number} (${substance.shortName})`;
        } else {
            header.innerText = `${substance.shortName}`;
        }

        subHeader.innerText = substance.longName;
        subHeader.classList.add("text-muted");

        annotationElement.classList.add("table", "table-small", "table-hover", "mt-5");

        const annotations = {
            "Amount": lot.amount,
            "Concentration / Purity": lot.purity,
            "Number of Aliquots": lot.numberOfAliquots,
            "Size / aliquot": lot.aliquotSize,
        };

        this.addPreviewTable(annotationElement, annotations);

        target.textContent = "";
        target.appendChild(header)
        target.appendChild(subHeader);
        target.appendChild(this.addAliquotLeftBar(lot.numberOfAliquots, lot.maxNumberOfAliquots));
        target.appendChild(annotationElement);
    }

    showWellContentCell(target, e) {
        const aliquot = e.object;
        const cell = e.object.cell;

        const header = document.createElement("h3");
        const subHeader = document.createElement("h4");
        const annotationElement = document.createElement("table");

        if (cell.number) {
            header.innerText = `${cell.number} | ${cell.name}`;
        } else {
            header.innerText = `${cell.name}`;
        }

        subHeader.classList.add("text-muted");
        subHeader.innerText = `Aliquot ${cell.number}.${aliquot.name}, p${aliquot.passage}`;

        annotationElement.classList.add("table", "table-small", "table-hover", "mt-5");

        const annotations = {
            "Aliquoted on": aliquot.aliquotedOn === null ? "unknown" : new Date(aliquot.aliquotedOn).toLocaleDateString(),
            "Aliquoted by": aliquot.aliquotedBy,
            "Mycoplasma Test": aliquot.mycoplasmaResult,
            "Cryomedium": aliquot.cryoMedium,
            "Vial colour": aliquot.vialColor,
            "Cell count": aliquot.cellCount,
        };

        this.addPreviewTable(annotationElement, annotations);

        target.textContent = "";
        target.appendChild(header)
        target.appendChild(subHeader);
        target.appendChild(this.addAliquotLeftBar(aliquot.numberOfAliquots, aliquot.maxNumberOfAliquots));
        target.appendChild(annotationElement);
    }

    addPreviewTable(table, annotations) {
        for (const [key, value] of Object.entries(annotations)) {
            let tr = document.createElement("tr")
            let dt = document.createElement("th");
            let dd = document.createElement("td");

            dt.innerText = key;
            dd.innerText = value;

            if (value) {
                tr.appendChild(dt);
                tr.appendChild(dd);
                table.append(tr);
            }
        }
    }

    addAliquotLeftBar(min, max) {
        const progress = document.createElement("div");
        progress.classList.add("progress");
        const progressBar = document.createElement("div");
        progressBar.classList.add("progress-bar");
        progressBar.setAttribute("role", "progressbar");
        progressBar.style.width = `${min/max*100}%`;
        progressBar.setAttribute("aria-valuenow", min.toString());
        progressBar.setAttribute("aria-valuemin", "0");
        progressBar.setAttribute("aria-valuemax", max.toString());
        progressBar.innerText = `${min}/${max}`;

        progress.appendChild(progressBar);
        return progress;
    }
}
