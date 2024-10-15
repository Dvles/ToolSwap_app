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

      // Handle the eventClick for updating event styles
      eventClick: function (info) {
        const startDateTime = new Date(info.event.start);
        const startDate = startDateTime.toISOString().split('T')[0]; // Format 'YYYY-MM-DD'

        // Update event properties (colors, etc.)
        info.event.setProp('borderColor', '#42f554'); // New border color
        info.event.setProp('textColor', '#ffffff');   // New text color
        info.event.setProp('backgroundColor', '#42a5f5'); // New background color

        // Debugging logs to check if properties are applied
        console.log("Event styles updated:", {
          borderColor: info.event.borderColor,
          textColor: info.event.textColor,
          backgroundColor: info.event.backgroundColor
        });

        // Update the borrowToolAvailabilities array
        borrowToolAvailabilities = borrowToolAvailabilities.map(event => {
          if (event.start === info.event.startStr && event.end === info.event.endStr) {
            return {
              ...event,
              borderColor: '#42f554',
              textColor: '#ffffff',
              backgroundColor: '#42a5f5',
            };
          }
          return event;
        });

        // Debugging logs
        console.log("Updated event with new styles:", info.event);
        console.log("Updated BorrowToolAvailabilities array:", borrowToolAvailabilities);
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
