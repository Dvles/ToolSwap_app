import './styles/calendar.css';
import { Calendar } from "@fullcalendar/core";
import interactionPlugin from "@fullcalendar/interaction";
import dayGridPlugin from "@fullcalendar/daygrid";
import axios from 'axios'; // Assuming axios is installed and imported

console.log('JavaScript file loaded');

// Function to format dates consistently
function formatDate(date) {
    if (date instanceof Date) {
        return date.toISOString().split('T')[0]; // Example formatting to 'YYYY-MM-DD'
    }
    return null; // Return null if date is invalid
}

document.addEventListener("DOMContentLoaded", function () {
    console.log('DOM fully loaded and parsed');

    // Get the calendar element by ID
    let calendarEl = document.getElementById("availabilityCalendar");
    console.log('Calendar element found:', calendarEl !== null);

    if (calendarEl) {
        const toolId = calendarEl.dataset.toolId;
        const toolName = calendarEl.dataset.toolName;
        console.log("Tool ID:", toolId);
        console.log("Tool Name:", toolName);

        if (!toolId || !toolName) {
            console.error("Tool ID or Tool Name is missing from the dataset.");
            return;
        }

        let evenementsJSONJS = calendarEl.dataset.calendar || '[]';
        console.log('Calendar data from dataset:', evenementsJSONJS);

        let evenementsJSONJSArray = [];
        try {
            evenementsJSONJSArray = JSON.parse(evenementsJSONJS);
            if (!Array.isArray(evenementsJSONJSArray)) {
                evenementsJSONJSArray = [];
            }
            console.log('Parsed events:', evenementsJSONJSArray);
        } catch (error) {
            console.error("Error parsing calendar data:", error);
            evenementsJSONJSArray = [];
        }

        let selectedToolAvailabilities = [];

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
            plugins: [interactionPlugin, dayGridPlugin],
            locale: 'fr', // Use the French locale
            buttonText: {
                today: 'Aujourd\'hui',
                month: 'Mois',
                week: 'Semaine',
                day: 'Jour'
            },
            dateClick: function (info) {
                const startDate = info.date;
                const endDate = info.date;

                console.log("Date clicked:", startDate);

                let newAvailability = {
                    toolId: toolId,
                    start: startDate,
                    end: endDate,
                    title: toolName,
                    allDay: true
                };

                calendar.addEvent(newAvailability);
                selectedToolAvailabilities.push(newAvailability);
                console.log("Added availability:", newAvailability);
            },

            eventClick: function (info) {
                console.log("Event clicked for removal:", info.event.title);

                info.event.remove();
                console.log("Event removed from calendar:", info.event.title);

                const index = selectedToolAvailabilities.findIndex(event => {
                    return event.start.getTime() === info.event.start.getTime();
                });

                if (index > -1) {
                    selectedToolAvailabilities.splice(index, 1);
                    console.log("Removed availability for event:", info.event.title);
                } else {
                    console.log("No matching availability found for event:", info.event.title);
                }
            }
        });

        calendar.render();
        console.log("Calendar rendered successfully");

        let confirmButton = document.getElementById("confirmAvailabilities");
        if (confirmButton) {
            confirmButton.addEventListener("click", function () {
                console.log("Confirm button clicked");

                axios.post(`/tool/add/availability/${toolId}`, selectedToolAvailabilities)
                    .then(response => {
                        console.log("Availability successfully added:", response.data);
                        alert("Availabilities saved successfully!");
                        window.location.href = `/tool/add/availability/${toolId}/success`;                    
                    })
                    .catch(error => {
                        console.error("Error saving availabilities:", error);
                        alert("Error saving availabilities. Please try again.");
                    });
            });
        } else {
            console.error("Confirm button not found");
        }
    } else {
        console.error("Calendar element not found.");
    }
});
