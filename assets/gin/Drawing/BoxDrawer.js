import * as d3 from "d3";

class BoxDrawer
{
    target;
    box;
    boxMap;

    constructor(target, box, boxMap, getCurrentValue, setCurrentValue)
    {
        this.target = target;
        this.box = box;
        this.boxMap = boxMap;
        this.getCurrentValue = getCurrentValue;
        this.setCurrentValue = setCurrentValue;
        this.initialValue = getCurrentValue();
        this.currentValue = getCurrentValue();
    }

    draw()
    {
        const rows = Math.max(1, this.box.rows);
        const cols = Math.max(1, this.box.cols);
        const clientWidth = this.target.clientWidth;
        const gridSize = 100;

        const width = gridSize*(cols);
        const height = gridSize*(rows);

        // Clean
        d3.select(this.target).selectAll("*").remove();

        const svgContainer = d3.select(this.target)
            .append("svg")
            .attr("viewBox", `0 0 ${width+gridSize+1} ${height+gridSize+1}`)
            .attr("preserveAspectRatio", "xMidYMid meet")
        ;

        let grid = svgContainer.append("g");

        grid.append("rect")
            .attr("width", width)
            .attr("height", height)
            .attr("x", gridSize)
            .attr("y", gridSize)
            .attr("stroke", "black")
            .attr("fill", "white");

        let positionFeedbackGroup = svgContainer
            .append("g");

        let positionFeedback = {
            "box": positionFeedbackGroup
                .append("rect")
                .attr("x", 0)
                .attr("y", 55)
                .attr("rx", 15)
                .attr("width", 95)
                .attr("height", 40)
                .attr("fill", "var(--bs-primary)")
            ,
            "text": positionFeedbackGroup
                .append("text")
                .attr("x", gridSize - 20)
                .attr("y", gridSize - 20)
                .attr("text-anchor", "end")
                .attr("dominant-baseline", "middle")
                .attr("font-size", gridSize/3)
                .attr("fill", "white")
                .append("tspan")
                .text(this.getCurrentValue())
            ,
        }

        this.cellMap = {};

        for (let rowIndex = 0; rowIndex <= rows; rowIndex++) {
            // Row labels
            if (rowIndex > 0) {
                grid.append("text")
                    .attr("x", gridSize - 20)
                    .attr("y", gridSize*(rowIndex+0.5))
                    .attr("text-anchor", "end")
                    .attr("dominant-baseline", "middle")
                    .attr("font-size", gridSize/3)
                    .append("tspan")
                    .text(`${this.rowCoordinateToLetter(rowIndex)}`)
            }

            for (let colIndex = 0; colIndex <= cols; colIndex++) {
                // Row labels
                if (rowIndex === 0 && colIndex > 0) {
                    grid.append("text")
                        .attr("x", gridSize*(colIndex+0.5))
                        .attr("y", gridSize - 20)
                        .attr("text-anchor", "middle")
                        .attr("dominant-baseline", "bottom")
                        .attr("font-size", gridSize/3)
                        .append("tspan")
                        .text(`${colIndex}`)
                }

                if (rowIndex > 0 && colIndex > 0) {
                    let mapEntry = this.boxMap.map[this.positionToIndex(rowIndex, colIndex)]
                    let cellGroup = grid.append("g");

                    let cell = cellGroup.append("rect")
                        .attr("width", gridSize)
                        .attr("height", gridSize)
                        .attr("x", colIndex*gridSize)
                        .attr("y", rowIndex*gridSize)
                        .attr("stroke", "black")
                        .attr("stroke-width", 1)
                    ;

                    this.cellMap[`${this.rowCoordinateToLetter(rowIndex)}${colIndex}`] = cell;

                    if (mapEntry.object !== null) {
                        cell.attr("fill", "lightgrey")
                    } else {
                        cell.attr("fill", "white")
                    }

                    let initialValue = this.letterCoordinatesToNumberCoordinates(this.initialValue)
                    if (rowIndex === initialValue[0] && colIndex === initialValue[1]) {
                        cell.attr("fill", "var(--bs-primary)");
                    }

                    cell
                        .on("mouseover", this.onBoxMouseOver.bind(this, positionFeedback, rowIndex, colIndex, mapEntry))
                        .on("mouseout", this.onBoxMouseOut.bind(this, positionFeedback, rowIndex, colIndex, mapEntry))
                        .on("click", this.onMouseClick.bind(this, positionFeedback, rowIndex, colIndex, mapEntry))
                        .on("touchstart", this.onMouseClick.bind(this, positionFeedback, rowIndex, colIndex, mapEntry))
                }
            }
        }
    }

    onBoxMouseOver(positionFeedback, rowIndex, colIndex, mapEntry)
    {
        let targetName = `${this.rowCoordinateToLetter(rowIndex)}${colIndex}`;
        positionFeedback.text.text(targetName);
        positionFeedback.box.attr("fill", "lightgrey");
    }

    onBoxMouseOut(positionFeedback, rowIndex, colIndex, mapEntry)
    {
        let targetName = `${this.rowCoordinateToLetter(rowIndex)}${colIndex}`;
        positionFeedback.text.text(this.getCurrentValue());
        positionFeedback.box.attr("fill", "var(--bs-primary)");
    }

    onMouseClick(positionFeedback, rowIndex, colIndex, mapEntry)
    {
        let targetName = `${this.rowCoordinateToLetter(rowIndex)}${colIndex}`;

        if (this.currentValue in this.cellMap) {
            if (mapEntry.object !== null) {
                this.cellMap[this.currentValue].attr("fill", "lightgrey");
            } else {
                this.cellMap[this.currentValue].attr("fill", "white");
            }
        }
        if (this.initialValue in this.cellMap) {
            this.cellMap[this.initialValue].attr("fill", "var(--bs-primary-bg-subtle)");
        }

        this.cellMap[targetName].attr("fill", "var(--bs-primary)");

        positionFeedback.text.text(targetName);
        positionFeedback.box.attr("fill", "var(--bs-primary)");

        this.currentValue = targetName;
        this.setCurrentValue(targetName);
    }

    positionToIndex(x, y)
    {
        return (x-1)*this.box.cols + (y-1);
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

    letterCoordinatesToNumberCoordinates(coordinates)
    {
        coordinates = coordinates.toUpperCase();
        let rowPart = "";
        let colPart = "";
        let letterLowerRange = "A".charCodeAt(0)
        let letterUpperRange = "Z".charCodeAt(0)
        let numberLowerRange = "0".charCodeAt(0)
        let numberUpperRange = "9".charCodeAt(0)

        for (let i = 0; i < coordinates.length; i++) {
            let l = coordinates.charAt(i);
            let ordL = l.charCodeAt(0)

            if (ordL >= letterLowerRange && ordL <= letterUpperRange) {
                rowPart += l;
            } else if (ordL >= numberLowerRange && ordL <= numberUpperRange) {
                colPart += l;
            }
        }

        let colNumber = Number(colPart);

        let rowNumber = 0;
        for (let i = 0; i < rowPart.length; i++) {
            let value = rowPart.charCodeAt(i) - letterLowerRange + 1;
            value = value * 26**(rowPart.length - i - 1)
            rowNumber += value
        }

        return [rowNumber, colNumber]
    }
}

export default BoxDrawer;