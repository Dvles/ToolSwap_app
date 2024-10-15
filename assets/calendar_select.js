/*
 * Main JavaScript file for calendar
 */

import './styles/calendar.css';
import { Calendar } from "@fullcalendar/core";
import interactionPlugin from "@fullcalendar/interaction";
import dayGridPlugin from "@fullcalendar/daygrid";
import axios from "axios";

document.addEventListener("DOMContentLoaded", function () {
  // Get the calendar element by ID
  let calendarEl = document.getElementById("availabilityCalendar");

  // Check if the element exists before accessing dataset
  if (calendarEl) {
    // Get the calendar data from the dataset
    let evenementsJSONJS = calendarEl.dataset.calendar;

    // Parse the JSON string into an array of event objects or use an empty array if no data
    let evenementsJSONJSArray = evenementsJSONJS ? JSON.parse(evenementsJSONJS) : [];

    // Tool ID and Tool Name (ensure these are being passed from the HTML)
    const toolId = calendarEl.dataset.toolId;
    const toolName = calendarEl.dataset.toolName;

    // Empty array to store ToolAvailabilities
    let borrowToolAvailabilities = [];

    // Debugging - log toolId and toolName to verify
    console.log("Dataset:", calendarEl.dataset);
    console.log("Tool ID:", toolId);
    console.log("Tool Name:", toolName);

    if (!toolId || !toolName) {
      console.error("Tool ID or Tool Name is missing from the dataset.");
      return;
    }

    // Initialize the FullCalendar
    var calendar = new Calendar(calendarEl, {
      events: evenementsJSONJSArray, // Load existing availabilities
      displayEventTime: false,
      initialView: "dayGridMonth",
      initialDate: new Date(),
      headerToolbar: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,timeGridWeek,timeGridDay",
      },

      // Handle the eventClick for updating event styles and adding/removing to array
      eventClick: function (info) {
        const startDateStr = info.event.start.toISOString().split('T')[0]; // Format 'YYYY-MM-DD'
        const eventId = info.event.id; // Use the unique ID from the event data
    
        // Check if the event is already in the array (deselection case)
        const index = borrowToolAvailabilities.findIndex(event => event.id === eventId);
        console.log("index: " + index);
        console.log("info.event.id: " + eventId);

        if (index !== -1) {
          // Event found, meaning the user is deselecting the date
          borrowToolAvailabilities.splice(index, 1); // Remove the event from the array

          // Revert the event's color to indicate deselection
          info.event.setProp('borderColor', '#ff0000'); // Reverted border color
          info.event.setProp('textColor', '#000000');   // Reverted text color
          info.event.setProp('backgroundColor', '#ffffff'); // Reverted background color

          console.log(`Deselected: ${startDateStr}, Updated Array:`, borrowToolAvailabilities);
        } else {
          // Event not found, meaning the user is selecting the date
          let selectedEvent = {
            id: eventId, // Use the existing unique ID
            toolId: toolId,
            start: startDateStr, // Use startDateStr for consistency
            end: startDateStr, // Use startDateStr for end as well
            title: toolName,
            borderColor: '#42f554',
            textColor: '#ffffff',
            backgroundColor: '#42a5f5',
            allDay: true
          };

          // Add the event to the array
          borrowToolAvailabilities.push(selectedEvent);

          // Update the event's color to indicate selection
          info.event.setProp('borderColor', '#42f554'); // New border color
          info.event.setProp('textColor', '#ffffff');   // New text color
          info.event.setProp('backgroundColor', '#42a5f5'); // New background color

          console.log(`Selected: ${startDateStr}, Updated Array:`, borrowToolAvailabilities);
        }
      },

      plugins: [interactionPlugin, dayGridPlugin],
    });


    // Render the calendar
    calendar.render();

    // Event listener for submitting tool availabilities
    document.getElementById("submitToolAvailabilities").addEventListener("click", function () {
      // Prepare data to send, keeping the structure of borrowToolAvailabilities
      const payload = borrowToolAvailabilities.map(avail => ({
        toolId: avail.toolId,
        start: avail.start,
        end: avail.end,
        title: avail.title,
        borderColor: avail.borderColor,
        textColor: avail.textColor,
        backgroundColor: avail.backgroundColor,
        allDay: avail.allDay
      }));

      // Debugging logs before sending to backend
      console.log("Submitting tool availabilities:", payload);

      // Send the data to the backend
      axios.post(`/tool/add/availability/${toolId}`, payload)
        .then(function (response) {
          // If successful, handle the response
          console.log("Event availability successfully stored:", response.data);
          calendar.refetchEvents(); // Refresh calendar events if needed
        })
        .catch(error => {
          console.log("Submitting tool availabilities:", payload);
          console.error("There was an error adding the event availability!", error);
        });
    });
  } else {
    console.error("Calendar element or data not found.");
  }
});
