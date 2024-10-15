/*
 * Main JavaScript file for calendar
 */

import './styles/calendar.css';
import { Calendar } from "@fullcalendar/core";
import interactionPlugin from "@fullcalendar/interaction";
import dayGridPlugin from "@fullcalendar/daygrid";
import axios from "axios";

// Alert to check if the JS file loads
alert('JavaScript file loaded');

document.addEventListener("DOMContentLoaded", function () {
  
  alert('DOM fully loaded and parsed');
  
  // Get the calendar element by ID
  let calendarEl = document.getElementById("availabilityCalendar");
  
  alert('Calendar element found: ' + (calendarEl !== null));

  // Ensure the calendar element exists
  if (calendarEl) {
    // Get the calendar data from the dataset
    let evenementsJSONJS = calendarEl.dataset.calendar || '[]'; // Default to '[]' if no data is provided

    alert('Calendar data from dataset: ' + evenementsJSONJS);

    // Parse the JSON string into an array of event objects, defaulting to an empty array
    let evenementsJSONJSArray = [];
    try {
      evenementsJSONJSArray = JSON.parse(evenementsJSONJS);
      if (!Array.isArray(evenementsJSONJSArray)) {
        evenementsJSONJSArray = []; // Ensure it's an array
      }
      alert('Parsed events: ' + JSON.stringify(evenementsJSONJSArray));
    } catch (error) {
      console.error("Error parsing calendar data:", error);
      evenementsJSONJSArray = []; // Fallback to an empty array
    }

    // Tool ID and Tool Name (ensure these are being passed from the HTML)
    const toolId = calendarEl.dataset.toolId;
    const toolName = calendarEl.dataset.toolName;

    // Debugging logs to verify tool ID and name
    console.log("Tool ID:", toolId);
    console.log("Tool Name:", toolName);

    if (!toolId || !toolName) {
      console.error("Tool ID or Tool Name is missing from the dataset.");
      return; // Exit early if required data is missing
    }

    // Initialize FullCalendar with default configuration
    var calendar = new Calendar(calendarEl, {
      events: evenementsJSONJSArray, // Event data from backend or empty array
      displayEventTime: false, // We don't need times, just dates
      initialView: "dayGridMonth", // Start with month view
      initialDate: new Date(), // Start on today's date
      headerToolbar: {
        left: "prev,next today", // Navigation buttons
        center: "title", // Calendar title
        right: "dayGridMonth,timeGridWeek,timeGridDay", // Different views
      },

      // Handle event click for deletion
      eventClick: function (info) {
        const startDate = new Date(info.event.start).toISOString().split('T')[0];

        // Confirm deletion
        if (confirm(`Are you sure you want to delete "${info.event.title}" availability on "${startDate}"?`)) {
          // Remove the event from the calendar
          info.event.remove();
          console.log("Event deleted:", info.event.title);
        }
      },

      // Handle date click for adding availability
      dateClick: function (info) {
        const startDate = info.dateStr;
        const endDate = info.dateStr; // Assuming start and end dates are the same

        // Debugging logs
        console.log("Date clicked:", startDate);

        // Prepare the new event data
        let nouvelEvenement = {
          toolId: toolId,
          start: startDate,
          end: endDate,
          title: toolName, // Tool name as event title
          allDay: true // This is an all-day event
        };

        // Add the event visually to the calendar
        calendar.addEvent(nouvelEvenement);
        console.log("Added event:", nouvelEvenement);
      },

      plugins: [interactionPlugin, dayGridPlugin], // Use interaction and day grid plugins
    });

    // Render the calendar
    calendar.render();
    console.log("Calendar rendered successfully");
    alert('Calendar rendered successfully');

  } else {
    console.error("Calendar element not found.");
    alert('Calendar element not found');
  }
});
