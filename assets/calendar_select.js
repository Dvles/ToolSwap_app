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

        // Ask user if they want to update the event's style
        if (confirm(`Do you want to update the availability for "${startDate}"?`)) {
          // Update event properties (colors, etc.)
          info.event.setProp('borderColor', '#42f554'); // New border color
          info.event.setProp('textColor', '#ffffff');   // New text color
          info.event.setProp('backgroundColor', '#42a5f5'); // New background color

          // Find the event in the borrowToolAvailabilities array and update it
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
        }
      },

      // When a date is clicked, add ToolAvailability
      dateClick: function (info) {
        const startDate = info.dateStr;
        const endDate = info.dateStr; // Assuming start and end dates are the same

        // Check for duplicates
        const exists = borrowToolAvailabilities.some(avail => 
          avail.start === startDate && avail.end === endDate
        );

        if (exists) {
          alert(`Event for ${startDate} already exists!`);
          return; // Exit the function if the event already exists
        }

        // Prepare the new event data
        let nouvelEvenement = {
          toolId: toolId, // toolId from the previous page
          start: startDate,
          end: endDate,
          title: toolName, // Set title from tool name
          borderColor: '#f59842', // Example border color
          textColor: '#000000', // Example text color
          backgroundColor: '#ffb775', // Example background color
          allDay: true // All-day event
        };

        // Store the event in the local array
        borrowToolAvailabilities.push(nouvelEvenement);

        // Debugging logs before sending to backend
        console.log("Attempting to add event:", nouvelEvenement);
        console.log("Current BorrowToolAvailabilities array:", borrowToolAvailabilities);

        // Add the event visually to the calendar
        calendar.addEvent({
          title: toolName,
          start: startDate,
          end: endDate,
          allDay: true,
          borderColor: '#ff9330', 
          textColor: '#000000', 
          backgroundColor: '#ffb775'
        });
      },

      plugins: [interactionPlugin, dayGridPlugin],
    });

    // Render the calendar
    calendar.render();

    // Event listener for submitting tool availabilities
    document.getElementById("submitToolAvailabilities").addEventListener("click", function() {
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
        .then(function(response) {
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
