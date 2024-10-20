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

        // Empty arrays to store modified & deleted availabilities
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
                console.log("existingEvent:", existingEvent);

                if (existingEvent) {
                    console.log("Found existing event:", existingEvent);

                    // Check if it exists in updateAvailabilities
                    const index = updateAvailabilities.findIndex(event => event.start.startsWith(dateStr));

                    if (index === -1) {
                        // Remove event from eventsJSONArray and calendar
                        let eventToRemove = calendar.getEventById(existingEvent.id);
                        if (eventToRemove) {
                            eventToRemove.remove();
                            deletedAvailabilities.push(existingEvent);  // Add to deletedAvailabilities
                            console.log("Removed Existing Event ID - from clicking on Date:", existingEvent.id);

                            // Remove event from eventsJSONArray
                            const eventIndex = eventsJSONArray.findIndex(event => event.id === existingEvent.id);
                            if (eventIndex !== -1) {
                                eventsJSONArray.splice(eventIndex, 1);  // Remove from the array
                                console.log("Removed event from eventsJSONArray:", eventsJSONArray);
                            } else {
                                console.warn("Event not found in eventsJSONArray.");
                            }
                        } else {
                            console.warn("No event found in calendar with ID:", existingEvent.id);
                        }
                    } else {
                        // Event exists in updateAvailabilities, remove it
                        let eventToRemove = updateAvailabilities.splice(index, 1)[0];  // Remove from updateAvailabilities
                        calendar.getEventById(eventToRemove.id)?.remove();  // Remove from front-end
                        console.log("Removed from Update Availabilities:", eventToRemove);
                    }
                } else {
                    console.info("Date is not present in eventsJSONArray");

                    // Check if the date exists in updateAvailabilities
                    const index = updateAvailabilities.findIndex(event => event.start.startsWith(dateStr));
                    console.log("Update Availabilities Index:", index);

                    if (index !== -1) {
                        // Remove from updateAvailabilities
                        let eventToRemove = updateAvailabilities.splice(index, 1)[0];  // Remove from updateAvailabilities
                        calendar.getEventById(eventToRemove.id)?.remove();  // Remove from front-end
                        console.log("Removed from Update Availabilities:", eventToRemove);
                    } else {
                        // Create new tool availability
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
                // Convert eventId to a number (since IDs in eventsJSONArray are numbers)
                const eventId = Number(info.event.id); // Convert to number

                console.log("Clicked Event ID:", eventId); // Log the clicked event ID

                // Check if the event is in eventsJSONArray (existing events from the DB)
                const indexInEvents = eventsJSONArray.findIndex(item => item.id === eventId);

                if (indexInEvents !== -1) {
                    // Remove from eventsJSONArray
                    let removedEvent = eventsJSONArray.splice(indexInEvents, 1)[0]; // Get the removed event

                    // Remove from the calendar and add to deletedAvailabilities
                    info.event.remove();

                    // Format the event similarly to 'updateAvailabilities' objects
                    const formattedDeletedEvent = {
                        id: removedEvent.id,
                        toolId: removedEvent.toolId,
                        title: removedEvent.title,
                        start: removedEvent.start,
                        end: removedEvent.end,
                        allDay: removedEvent.allDay,
                        backgroundColor: removedEvent.backgroundColor,
                        borderColor: removedEvent.borderColor,
                        textColor: removedEvent.textColor
                    };

                    // Add to deletedAvailabilities
                    deletedAvailabilities.push(formattedDeletedEvent);

                } else {
                    // Check updateAvailabilities (newly added events)
                    const indexInUpdates = updateAvailabilities.findIndex(item => item.id === eventId);

                    if (indexInUpdates !== -1) {
                        // Event exists in updateAvailabilities
                        let eventToRemove = updateAvailabilities.splice(indexInUpdates, 1)[0]; // Get the removed event

                        // Remove from the calendar
                        calendar.getEventById(eventToRemove.id)?.remove();
                    } else {
                        console.warn("Event not found in either eventsJSONArray or updateAvailabilities.");
                    }
                }
            },

            plugins: [interactionPlugin, dayGridPlugin],
        });

        calendar.render();

        let btnCheck = document.getElementById("btnCheck");

        btnCheck.addEventListener("click", function () {
            console.log("deletedAvailabilities:", deletedAvailabilities);
            console.log("updateAvailabilities:", updateAvailabilities);
        });

        // Get the confirm button
        let btnUpdateAvailabilities = document.getElementById("btnUpdateAvailabilities");
        if (!btnUpdateAvailabilities) {
            console.error("Confirm link not found in the DOM.");
            return;
        }

        // Event listener for the confirm button
        btnUpdateAvailabilities.addEventListener("click", function (event) {
            event.preventDefault();
            console.log("Confirm button clicked.");

            // Check if updateAvailabilities OR deletedAvailabilities has data
            if (updateAvailabilities.length === 0 && deletedAvailabilities.length === 0) {
                console.warn("No tool availability updated.");
                return; // Exit early if no data
            }

            // Sending the data to the backend
            axios.post(`tool/update/availability/${tool_id}/confirm`, {
                availabilities: updateAvailabilities,
                deletedAvailabilities: deletedAvailabilities
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
                console.error("Error updating availabilities:", error);
            });
        });
    } else {
        console.log("Calendar not initialized");
    }
});
