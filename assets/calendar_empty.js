/*
 * Main JavaScript file for calendar
 */

import './styles/calendar.css';
import { Calendar } from "@fullcalendar/core";
import interactionPlugin from "@fullcalendar/interaction";
import dayGridPlugin from "@fullcalendar/daygrid";
import axios from 'axios'; // Assuming axios is installed and imported

console.log('JavaScript file loaded');

document.addEventListener("DOMContentLoaded", function () {
  
  console.log('DOM fully loaded and parsed');
  
  // Get the calendar element by ID
  let calendarEl = document.getElementById("availabilityCalendar");
  console.log('Calendar element found:', calendarEl !== null);

  // Ensure the calendar element exists
  if (calendarEl) {
    // Check data attributes
    const toolId = calendarEl.dataset.toolId;
    const toolName = calendarEl.dataset.toolName;
    console.log("Tool ID:", toolId);
    console.log("Tool Name:", toolName);

    // Check if both attributes exist
    if (!toolId || !toolName) {
      console.error("Tool ID or Tool Name is missing from the dataset.");
      return; // Exit early if required data is missing
    }

    // Get the calendar data from the dataset
    let evenementsJSONJS = calendarEl.dataset.calendar || '[]'; // Default to '[]' if no data is provided
    console.log('Calendar data from dataset:', evenementsJSONJS);

    // Parse the JSON string into an array of event objects
    let evenementsJSONJSArray = [];
    try {
      evenementsJSONJSArray = JSON.parse(evenementsJSONJS);
      if (!Array.isArray(evenementsJSONJSArray)) {
        evenementsJSONJSArray = []; // Ensure it's an array
      }
      console.log('Parsed events:', evenementsJSONJSArray);
    } catch (error) {
      console.error("Error parsing calendar data:", error);
      evenementsJSONJSArray = []; // Fallback to an empty array
    }

    // Array to store selected tool availabilities
    let selectedToolAvailabilities = [];

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
      plugins: [interactionPlugin, dayGridPlugin], // Use interaction and day grid plugins

      // Handle date click for adding tool availability
      dateClick: function (info) {
        const startDate = info.dateStr;
        const endDate = info.dateStr; // Assuming start and end dates are the same

        console.log("Date clicked:", startDate);

        // Prepare the new event data
        let newAvailability = {
          toolId: toolId,
          start: startDate,
          end: endDate,
          title: toolName, // Tool name as event title
          allDay: true // This is an all-day event
        };

        // Add the event to the calendar visually
        calendar.addEvent(newAvailability);

        // Store the selected availability in the array
        selectedToolAvailabilities.push(newAvailability);
        console.log("Added availability:", newAvailability);
        console.log("Current selected availabilities:", selectedToolAvailabilities);
      },

      // Handle event click for removing availability
      eventClick: function (info) {
        const clickedStartDate = info.event.start.toISOString().split('T')[0];
        console.log("Event clicked for removal:", clickedStartDate);
        // Remove the event from FullCalendar view
        info.event.remove();
        console.log("Event removed from calendar:", info.event.title);

        // Remove the corresponding event from the selectedToolAvailabilities array
        selectedToolAvailabilities = selectedToolAvailabilities.filter(event => event.start !== clickedStartDate);
        console.log("Updated availabilities after removal:", selectedToolAvailabilities);

      }
    });

    // Render the calendar
    calendar.render();
    console.log("Calendar rendered successfully");

    // Handle the "Confirm" button click to send the selected availabilities to the backend
    let confirmButton = document.getElementById("confirmAvailabilities");
    if (confirmButton) {
      confirmButton.addEventListener("click", function () {
        console.log("Confirm button clicked");

        // Send the array of selected availabilities to the backend via POST request
        axios.post(`/tool/add/availability/${toolId}`, selectedToolAvailabilities)
          .then(response => {
            console.log("Availability successfully added:", response.data);
            alert("Availabilities saved successfully!");
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
