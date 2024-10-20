import './styles/calendar.css';
import { Calendar } from "@fullcalendar/core";
import interactionPlugin from "@fullcalendar/interaction";
import dayGridPlugin from "@fullcalendar/daygrid";
import axios from "axios";

document.addEventListener("DOMContentLoaded", function () {
    let calendarEl = document.getElementById("availabilityCalendar");

    if (calendarEl && calendarEl.dataset.calendar) {

        // Get the calendar data from the dataset
        let eventsArray = calendarEl.dataset.calendar;

        // Parse the JSON string into an array of event objects
        let eventsJSONArray = JSON.parse(eventsArray);

        //let eventsArray = calendarEl.dataset.calendar ? JSON.parse(calendarEl.dataset.calendar) : [];
        const toolId = calendarEl.dataset.toolId;
        const toolName = calendarEl.dataset.toolName;


        // Debugging logs
        console.log("Tool ID:", toolId);
        console.log("Tool Name:", toolName);
        console.log("eventsArray:", eventsArray);
        console.log("eventsJSONArray:", eventsJSONArray);


        // Empty array to store modified & deleted Availabilities
        let modifiedAvailabilities = [];
        let deletedAvailabilities = [];

        var calendar = new Calendar(calendarEl, {
            events: eventsJSONArray,
            displayEventTime: false,
            initialView: "dayGridMonth",
            initialDate: new Date(),
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: "dayGridMonth,timeGridWeek,timeGridDay",
            },
            dateClick: function (info) {
                // Adding new availability
                const dateStr = info.dateStr;
                let newEvent = {
                    id: Date.now(), // temporary ID
                    toolId: toolId,
                    start: dateStr,
                    end: dateStr,
                    title: toolName,
                    borderColor: '#42f554',
                    textColor: '#ffffff',
                    backgroundColor: '#42a5f5',
                    allDay: true,
                    isNew: true // mark this as a new event
                };
                modifiedAvailabilities.push(newEvent);
                calendar.addEvent(newEvent);
            },
            eventClick: function (info) {
                // Remove existing availability
                const eventId = info.event.id;
                modifiedAvailabilities = modifiedAvailabilities.filter(event => event.id !== eventId);
                info.event.remove();
                deletedAvailabilities.push(newEvent);

            },
            plugins: [interactionPlugin, dayGridPlugin],
        });

        calendar.render();

        document.getElementById("confirmModifiedAvailabilities").addEventListener("click", function (event) {
            event.preventDefault();

            axios.post(`/tool/update/availability/${tool_id}`, {
                availabilities: modifiedAvailabilities
            }, {
                headers: {
                    'Content-Type': 'application/json'
                }
            })
                .then(response => {
                    // Handle redirect after success
                    window.location.href = response.request.responseURL || '/success';
                })
                .catch(error => {
                    console.error("Error updating availabilities:", error);
                });
        });
    } else {
        console.log("calendar not initialized");
        let eventsArray = calendarEl.dataset.calendar ? JSON.parse(calendarEl.dataset.calendar) : [];
        console.log("eventsArray:", eventsArray);


    }
});
