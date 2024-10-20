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
        console.log("Initial eventsJSONArray:", JSON.stringify(eventsJSONArray, null, 2));
        const toolId = calendarEl.dataset.toolId;
        const toolName = calendarEl.dataset.toolName;

        // Empty arrays to store modified & deleted Availabilities
        let updateAvailabilities = [];
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
                const dateStr = info.dateStr;  // Clicked date as a string (e.g., "2024-10-17")
                console.log("Date clicked:", dateStr);  // Log clicked date
            
                // Check if the clicked date is already in eventsJSONArray (existing events from DB)
                let existingEvent = eventsJSONArray.find(event => event.start.startsWith(dateStr));
                console.log("Found existing event:", existingEvent);
            
                // Check if we found an existing event
                if (existingEvent) {
                    // Now check if it exists in updateAvailabilities
                    const index = updateAvailabilities.findIndex(event => event.start.startsWith(dateStr));
            
                    if (index === -1) {
                        // This means the existing event is not in updateAvailabilities
                        // Attempt to remove it from the calendar
                        let eventToRemove = calendar.getEventById(existingEvent.id);
                        if (eventToRemove) {
                            eventToRemove.remove();
                            deletedAvailabilities.push(existingEvent);
                            console.log("Removed Existing Event ID:", existingEvent.id);
                            console.log("deletedAvailabilities:", deletedAvailabilities);
                        } else {
                            console.warn("No event found in calendar with ID:", existingEvent.id);
                        }
                    } else {
                        // If the event exists in updateAvailabilities, we can just remove it from that array
                        let eventToRemove = updateAvailabilities[index];
                        updateAvailabilities.splice(index, 1);  // Remove from updateAvailabilities
                        calendar.getEventById(eventToRemove.id)?.remove();  // Remove from front-end
                        console.log("Removed from Update Availabilities:", eventToRemove);
                    }
                } else {
                    // Date is not present in eventsJSONArray, so check updateAvailabilities
                    const index = updateAvailabilities.findIndex(event => event.start.startsWith(dateStr));
                    console.log("Update Availabilities Index:", index);
            
                    if (index !== -1) {
                        // Date already exists in the front end (updateAvailabilities), remove it
                        let eventToRemove = updateAvailabilities[index];
                        updateAvailabilities.splice(index, 1);  // Remove from updateAvailabilities
                        calendar.getEventById(eventToRemove.id)?.remove();  // Remove from front-end
                        console.log("Removed from Update Availabilities:", eventToRemove);
                    } else {
                        // Date is not present, create a new tool availability 
                        let newEvent = {
                            id: Date.now(), // temporary ID for front-end usage
                            toolId: toolId,
                            title: toolName,
                            start: `${dateStr}T00:00:00+01:00`, // Format start date as ISO string
                            end: `${dateStr}T23:59:59+01:00`, 
                            borderColor: '#42f554',
                            textColor: '#ffffff',
                            backgroundColor: '#42a5f5',
                            allDay: true,
                            isNew: true // mark this as a new event
                        };
            
                        // Add the new availability to updateAvailabilities and the calendar
                        updateAvailabilities.push(newEvent);
                        calendar.addEvent(newEvent);
                        console.log("Added New Event:", newEvent);
                    }
                }
            },

            eventClick: function (info) {
                // Convert eventId to a number
                const eventId = Number(info.event.id); // Convert to number
            
                console.log("Clicked Event ID:", eventId); // Log the clicked event ID
                console.log("Before Removal - eventsJSONArray:", JSON.stringify(eventsJSONArray, null, 2)); // Log state before removal
            
                // Remove from eventsJSONArray
                const index = eventsJSONArray.findIndex(item => item.id === eventId);
            
                if (index !== -1) { // Check if the item was found
                    eventsJSONArray.splice(index, 1); // Remove the item at that index
                    console.log("Removed from eventsJSONArray:", JSON.stringify(eventsJSONArray, null, 2));
                } else {
                    console.warn("Event ID not found in eventsJSONArray");
                }
            
                // Remove the event from the calendar
                info.event.remove();
            
                // Push to deletedAvailabilities for tracking
                deletedAvailabilities.push(info.event);

                // debugging
                console.log("Deleted Event ID:", eventId);
                console.log("deletedAvailabilities:", deletedAvailabilities);
                console.log("After Removal - eventsJSONArray:", JSON.stringify(eventsJSONArray, null, 2));  // Log the updated array
            },

            plugins: [interactionPlugin, dayGridPlugin],
        });

        calendar.render();

        document.getElementById("confirmupdateAvailabilities").addEventListener("click", function (event) {
            event.preventDefault();

            axios.post(`/tool/update/availability/${toolId}`, {
                availabilities: updateAvailabilities,
                deletedAvailabilities: deletedAvailabilities
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
    }
});
