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

    // empty array to store ToolAvailabilities
    let toolAvailabilities = [];

    // Debugging - log toolId and toolName to verify
    console.log("Tool ID:", toolId);
    console.log("Tool Name:", toolName);

    if (!toolId || !toolName) {
      console.error("Tool ID or Tool Name is missing from the dataset.");
      return;
    }

    // Initialize the FullCalendar
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

      // When a date is clicked, add ToolAvailability
      dateClick: function (info) {
        const startDate = info.dateStr;
        const endDate = info.dateStr; // Assuming start and end dates are the same

        // Prepare the new event data
        let nouvelEvenement = {
          toolId: toolId, // toolId from the previous page
          start: startDate,
          end: endDate,
          title: toolName, // Set title from tool name
          borderColor: '#ff0000', // Example border color
          textColor: '#ffffff', // Example text color
          backgroundColor: '#ffcccb', // Example background color
          allDay: true // All-day event
        };

        toolAvailabilities.push(nouvelEvenement);

        // Debugging logs before sending to backend
        console.log("Attempting to add event:", nouvelEvenement);

        // Add to backend
        axios.post(`/tool/add/availability/${toolId}`, nouvelEvenement)
          .then(function (response) {
            // If successful, set the ID from the response
            nouvelEvenement.id = response.data.id; // Ensure response returns ID
            calendar.addEvent(nouvelEvenement);
            console.log("Event added successfully:", nouvelEvenement);
            console.log("ToolAvailabilities: ", toolAvailabilities);
          })
          .catch(error => {
            console.error("toolId: ", toolId);
            console.error("name: ", toolName);
            console.error("startdate: ", startDate);
            console.error("enddate: ", endDate);
            console.error("There was an error adding the event!", error);
          });
      },

      plugins: [interactionPlugin, dayGridPlugin],
    });

    // Render the calendar
    calendar.render();

    // Event listener for submitting tool availabilities
    document.getElementById("submitToolAvailabilities").addEventListener("click", function() {
      const nouvelEvenement = toolAvailabilities.map(avail => ({
          title: avail.title,
          start: avail.start, // Format this if needed
          end: avail.end,     // Format this if needed
          backgroundColor: avail.backgroundColor, // Include if needed
          borderColor: avail.borderColor,         // Include if needed
          textColor: avail.textColor               // Include if needed
      }));

      // Send the data to the backend
      axios.post(`/tool/add/availability/${toolId}`, nouvelEvenement)
        .then(function(response) {
            // If successful, handle the response
            console.log("Event availability successfully stored:", nouvelEvenement);
            // You may want to do something here to refresh the calendar or the UI
        })
        .catch(error => {
            console.error("There was an error adding the event!", error);
        });
    });
    
  } else {
    console.error("Calendar element or data not found.");
  }
});
