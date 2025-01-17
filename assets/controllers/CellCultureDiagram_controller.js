import {Controller} from "@hotwired/stimulus";
import * as d3 from "d3";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        cultures: Object,
        startDate: String,
        endDate: String,
        leftMargin: Number,
    }

    static targets = [
        "diagram",
        "events",
    ];

    initialize() {
        super.initialize();
    }

    connect() {
        const heightPerEntry = 100;
        const chart = this.diagramTarget;
        const eventArea = this.diagramEvents;
        const totalChartWidth = chart.clientWidth;
        const cultures = Object.values(this.culturesValue);
        const numberOfCultures = cultures.length;

        let margin = {};
        let width = 0;
        let height = 0;

        // Decide on which screen we are
        if (totalChartWidth < 768) {
            // Small screens
            margin = {top: 60, right: 10, bottom: 30, left: this.leftMarginValue > 60 ? 60 : this.leftMarginValue}
            width = totalChartWidth - margin.left - margin.right
            height = heightPerEntry*numberOfCultures;
        } else {
            // Large screens
            margin = {top: 60, right: 20, bottom: 30, left: this.leftMarginValue}
            width = totalChartWidth - margin.left - margin.right
            height = heightPerEntry*numberOfCultures;
        }

        // Make sure we have a minimum width for the plot. Area should be scrollable.
        width = Math.max(width, 600);

        // append the svg object to the body of the page
        const svg = d3.select(chart)
            .append("svg")
            .attr("width", width + margin.left + margin.right)
            .attr("height", height + margin.top + margin.bottom)
            .append("g")
            .attr("transform",
                "translate(" + margin.left + "," + margin.top + ")");

        const dateFormat = d3.timeFormat("%Y-%m-%d");
        const dateFormatAxis = d3.timeFormat("%d %b");
        const dateParse = d3.timeParse("%Y-%m-%d");

        const startDate = dateParse(this.startDateValue);
        const endDate = dateParse(this.endDateValue);
        const pseudoEndDate = dateParse("2023-08-15");
        const timeDelta = ((endDate - startDate) / 1000 / 60 / 60 / 24) + 1

        const timeAxis = d3.scaleTime()
            .domain([startDate, endDate])
            .range([0, width]);

        const xAxis = d3.axisBottom(timeAxis)
            .tickFormat(dateFormatAxis);

        // Add the row number to each cell culture
        let i = 0;
        for (const [key, value] of Object.entries(cultures)) {
            cultures[key].i = i;
            cultures[key].trashedOn = cultures[key].trashedOn ? d3.isoParse(cultures[key].trashedOn) : null;
            cultures[key].unfrozenOn = cultures[key].unfrozenOn ? d3.isoParse(cultures[key].unfrozenOn) : null;
            i++;
        }

        // Add axis
        svg.append("g")
            .attr("class", "x axis")
            .attr("transform", "translate(0," + (height-50) + ")")
            .call(xAxis)
        ;

        const defs = svg
            .append("defs");

        // Warning signal if mycoplasma status is positive
        defs
            .append("g")
            .attr("id", "mycoplasma-status-positive")
            .append("circle")
            .attr("r", 12.5)
            .attr("stroke-width", 2)
            .attr("stroke", "red")
            .attr("fill", "white")
        ;

        // OK sign if mycoplasma status is negative
        defs
            .append("g")
            .attr("id", "mycoplasma-status-negative")
            .append("circle")
            .attr("r", 12.5)
            .attr("stroke-width", 1)
            .attr("stroke", "black")
            .attr("fill", "#6ed36e")
        ;

        // Warning sign if mycoplasma status is unclear
        defs
            .append("g")
            .attr("id", "mycoplasma-status-unclear")
            .append("path")
            .attr("d", "M-12.5 10.825 L12.5 10.825 L0 -10.825 Z")
            .attr("stroke-width", 1)
            .attr("stroke", "black")
            .attr("fill", "#ffff66")
        ;

        // Abandoned sign
        defs
            .append("g")
            .attr("id", "culture-is-abandoned")
            .append("path")
            .attr("d", "M-12.5 -10.825 L12.5 -10.825 L0 10.825 Z")
            .attr("stroke-width", 1)
            .attr("stroke", "black")
            .attr("fill", "orange")
        ;
        // For each cell line, create a group and save it as "rows"
        const rows = svg.append('g')
            .selectAll("dot")
            .data(cultures)
            .enter()
            .append("g")
        ;

        const textbox = rows.append("text")
            .attr("x", -margin.left)
            .attr("y", (d) => -40 + heightPerEntry * d.i)
            .attr("text-anchor", "start")
            .attr("text-height", 14)
        ;

        const title = textbox
            .append("tspan")
            .attr("font-weight", "bold")
            .attr("font-size", 14)
            .text(d => d.name)
        ;

        const additionalInformation = textbox
            .append("tspan")
            .attr("font-size", 12)
            .attr("dy", 14)
            .attr("x", -margin.left)
            .text(d => ` Incubator: ${d.incubator}, Scientist: ${d.owner.fullName}`)
        ;

        rows.append("polyline")
            .attr("points", function (d, i) {
                let xStart = timeAxis(d.unfrozenOn < startDate ? startDate : d.unfrozenOn);
                let xEnd = timeAxis(d.trashedOn ? (d.trashedOn > endDate ? endDate : d.trashedOn) : endDate);
                let y = i * heightPerEntry;

                return `${xStart},${y} ${xEnd},${y}`;
            })
            .attr("stroke", "black")
            .style("stroke-width", 3);

        // Start point
        rows
            .filter(function(d) { return d.unfrozenOn >= startDate})
            .append("circle")
            .attr("cx", function (d) { return timeAxis(d.unfrozenOn); } )
            .attr("cy", function (d) { return d.i * heightPerEntry} )
            .attr("r", 5)
            .style("fill", "#000000");

        rows
            .filter(function(d) { return d.unfrozenOn >= startDate}).append("text")
            .text(function (d) { return d.startPassage; })
            .attr("x", function (d) { return timeAxis(d.unfrozenOn); } )
            .attr("y", function (d) { return d.i * heightPerEntry -8 })
            .attr("font-size", 11)
            .attr("text-anchor", "middle")
            .attr("text-height", 14)
        ;

        // End point
        rows
            .filter(function (d) { return !!d.trashedOn; })
            .append("circle")
            .attr("cx", function (d) { return timeAxis(d.trashedOn); } )
            .attr("cy", function (d) { return d.i * heightPerEntry} )
            .attr("r", 5)
            .style("fill", "#000000")
        ;

        rows
            .filter(function (d) { return !!d.trashedOn; })
            .append("text")
            .text(function (d) { return d.endPassage; })
            .attr("x", function (d) { return timeAxis(d.trashedOn);})
            .attr("y", function (d) { return d.i * heightPerEntry -8 })
            .attr("font-size", 11)
            .attr("text-anchor", "middle")
            .attr("text-height", 14)
        ;

        // Events
        const event_group = rows
            .append("g")
            .selectAll("g")
            .data(function (d) {
                let events = [];
                let event_dates = {};
                const keyFormatter = d3.timeFormat("%Y%m%d");

                for (const e of d.events) {
                    e.date = e.date ? d3.isoParse(e.date) : null;
                    e.i = d.i;

                    if (!(e.date && e.date >= startDate && e.date <= endDate)) {
                        continue;
                    }

                    let key = keyFormatter(e.date);

                    if (key in event_dates) {
                        event_dates[key] += 1;
                        e.jitter = event_dates[key];
                    } else {
                        event_dates[key] = 0;
                        e.jitter = 0;
                    }

                    events.push(e);
                }

                return events;
            })
            .enter()
            .append("g")
        ;

        function jitterOffset(d) {
            //return (d.jitter === 0 ? 0 : (d.jitter%2 ? d.jitter * -10 : d.jitter * 10 - 10);
            return d.jitter * 10;
        }

        const testEvents = event_group.filter(function (d) { return d.eventType === "App\\Entity\\DoctrineEntity\\Cell\\CellCultureTestEvent" })
            .append("circle")
            .attr("cx", function (d) { return timeAxis(d.date); } )
            .attr("cy", function (d) { return d.i * heightPerEntry + jitterOffset(d); })
            .attr("r", 5)
            .style("fill", function (d) { return d.result === "positive" ? "#FF0000" : (d.result === "negative" ? "#00FF00" : "#FFFF00")})
            .style("stroke", "black")
            .style("stroke-width", 1)
        ;

        let hideOnMouseOutEvent = function(event) {
            this.eventsTarget.style.display = "none";
        }.bind(this);

        testEvents.on("mouseover", function(event, data) {
            let content = "";
            let output = this.eventsTarget;
            let position = {x: 0, y: 0}

            position.x = event.pageX;
            position.y = event.pageY + 10;

            content += "<b>" + dateFormat(data.date) + "</b><br />"
            content += data.shortName + "<br />";
            content += data.description ? data.description + "<br />" : "<br />";
            content += "<b>Scientist:</b> " + data.owner.fullName + "<br />";
            content += "<b>Test type:</b> " + data.testType + "<br />";
            content += "<b>Test result:</b> " + data.result + "<br />";

            output.innerHTML = content;
            output.style.top = position.y + "px";
            output.style.left = position.x + "px";
            output.style.display = "block";
        }.bind(this)).on("mouseout", hideOnMouseOutEvent);

        const splitEvents = event_group.filter(function (d) { return d.eventType === "App\\Entity\\DoctrineEntity\\Cell\\CellCultureSplittingEvent" })
            .append("circle")
            .attr("cx", function (d) { return timeAxis(d.date); } )
            .attr("cy", function (d) { return d.i * heightPerEntry + jitterOffset(d); })
            .attr("r", 5)
            .style("fill", "#FFFFFF")
            .style("stroke", "black")
            .style("stroke-width", 1)
        ;

        event_group.filter(function (d) { return d.eventType === "App\\Entity\\DoctrineEntity\\Cell\\CellCultureSplittingEvent" })
            .append("text")
            .text(function (d) { return d.currentPassage; })
            .attr("x", function (d) { return timeAxis(d.date);})
            .attr("y", d => d.i * heightPerEntry -8)
            .attr("font-size", 11)
            .attr("text-anchor", "middle")
            .attr("text-height", 14)
        ;

        splitEvents.on("mouseover", function (event, data) {
            let content = "";
            let output = this.eventsTarget;
            let position = {x: 0, y: 0}

            position.x = event.pageX;
            position.y = event.pageY + 10;

            content += "<b>" + dateFormat(data.date) + "</b><br />";
            content += data.shortName + "<br />";
            content += data.description ? data.description + "<br />" : "<br />";
            content += "<b>Scientist:</b> " + data.owner.fullName + "<br />";
            content += "<b>Splitting:</b> " + data.splitting + "<br />";
            content += "<b>New Flask:</b> " + data.newFlask + "<br />";

            output.innerHTML = content;
            output.style.top = position.y + "px";
            output.style.left = position.x + "px";
            output.style.display = "block";
        }.bind(this)).on("mouseout", hideOnMouseOutEvent);

        const otherEvents = event_group.filter(function (d) { return d.eventType === "App\\Entity\\DoctrineEntity\\Cell\\CellCultureOtherEvent" })
            .append("circle")
            .attr("cx", function (d) { return timeAxis(d.date); } )
            .attr("cy", function (d) { return d.i * heightPerEntry + jitterOffset(d); })
            .attr("r", 5)
            .style("fill", "#999999")
            .style("stroke", "black")
            .style("stroke-width", 1)
        ;

        otherEvents.on("mouseover", function (event, data) {
            let content = "";
            let output = this.eventsTarget;

            let position = {x: 0, y: 0}
            position.x = event.pageX;
            position.y = event.pageY + 10;

            content += "<b>" + dateFormat(data.date) + "</b><br />";
            content += data.shortName + "<br />";
            content += data.description ? data.description + "<br />" : "<br />";
            content += "<b>Scientist:</b> " + data.owner.fullName + "<br />";

            output.innerHTML = content;
            output.style.top = position.y + "px";
            output.style.left = position.x + "px";
            output.style.display = "block";
        }.bind(this)).on("mouseout", hideOnMouseOutEvent);
    }
}