import './styles/calendar.css';
import { Calendar } from "@fullcalendar/core";
import interactionPlugin from "@fullcalendar/interaction";
import dayGridPlugin from "@fullcalendar/daygrid";
import axios from "axios";

document.addEventListener("DOMContentLoaded", function () {
    console.log("DOMContentLoaded event fired");

    // Get the calendar element
    let calendarEl = document.getElementById("availabilityCalendar");

    // Check if the calendar element exists
    if (calendarEl) {
        console.log("Calendar element found.");
        let evenementsJSONJS = calendarEl.dataset.calendar;

        // Parse the JSON string into an array of event objects
        let evenementsJSONJSArray = evenementsJSONJS ? JSON.parse(evenementsJSONJS) : [];
        console.log("evenementsJSONJSArray", evenementsJSONJSArray);

        // Tool ID and Tool Name
        const toolId = calendarEl.dataset.toolId;
        const toolName = calendarEl.dataset.toolName;

        // Empty array to store ToolAvailabilities
        let borrowToolAvailabilities = [];

        // Debugging logs
        console.log("Tool ID:", toolId);
        console.log("Tool Name:", toolName);

        if (!toolId || !toolName) {
            console.error("Tool ID or Tool Name is missing from the dataset.");
            return;
        }

        // Initialize FullCalendar
        var calendar = new Calendar(calendarEl, {
            events: evenementsJSONJSArray,
            displayEventTime: false,
            initialView: "dayGridMonth",
            initialDate: new Date(),
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: "dayGridMonth,timeGridWeek,timeGridDay",
            },
            eventClick: function (info) {
                const startDateStr = info.event.start.toISOString().split('T')[0];
                const eventId = info.event.id;
                const index = borrowToolAvailabilities.findIndex(event => event.id === eventId);

                if (index !== -1) {
                    borrowToolAvailabilities.splice(index, 1);
                    // Revert color
                    info.event.setProp('borderColor', '#ff0000');
                    info.event.setProp('textColor', '#000000');
                    info.event.setProp('backgroundColor', '#ffffff');
                    console.log('index!== 1 - borrowToolAvailabilities:', borrowToolAvailabilities);
                } else {
                    let selectedEvent = {
                        id: eventId,
                        toolId: toolId,
                        start: startDateStr,
                        end: startDateStr,
                        title: toolName,
                        borderColor: '#42f554',
                        textColor: '#ffffff',
                        backgroundColor: '#42a5f5',
                        allDay: true
                    };
                    borrowToolAvailabilities.push(selectedEvent);
                    info.event.setProp('borderColor', '#42f554');
                    info.event.setProp('textColor', '#ffffff');
                    info.event.setProp('backgroundColor', '#42a5f5');
                    console.log('index == 1 - borrowToolAvailabilities:', borrowToolAvailabilities);

                }
                console.log(`Updated Array:`, borrowToolAvailabilities);
            },
            plugins: [interactionPlugin, dayGridPlugin],
        });

        // Render the calendar
        calendar.render();
        console.log("FullCalendar rendered.");

        // Get the confirm button
        let confirmLink = document.getElementById("confirmLink");
        if (!confirmLink) {
            console.error("Confirm link not found in the DOM.");
            return;
        }

        console.log(confirmLink);
        // Event listener for the confirm button
        confirmLink.addEventListener("click", function (event) {
            event.preventDefault(); // Prevent default button behavior
            console.log("Confirm button clicked.");

            // Check if borrowToolAvailabilities has data
            if (borrowToolAvailabilities.length === 0) {
                console.warn("No availability selected! Please select at least one date.");
                return; // Exit early if no data
            }

            // Convert borrowToolAvailabilities to JSON string
            const additionalData = JSON.stringify(borrowToolAvailabilities);
            console.log("Additional data to be sent:", additionalData);

            // Send the data using axios
            axios.post(`/tool/single/${toolId}/borrow/calendar/confirm`, {
                availabilities: borrowToolAvailabilities
            }, {
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                console.log("Response from server:", response.data);
                // Check if there is a redirect URL in the response
                if (response.request.responseURL) {
                    // Perform the redirection
                    window.location.href = response.request.responseURL;
                } else {
                    console.log("No redirect URL detected. Handle success message or logic here.");
                }
            })
            .catch(error => {
                console.error("There was an error sending the data:", error);
            });
        });

    } else {
        console.error("Calendar element not found.");
    }
    
    console.log("Script executed.");
});
