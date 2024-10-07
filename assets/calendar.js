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
  if (calendarEl && calendarEl.dataset.calendar) {
    // Get the calendar data from the dataset
    let evenementsJSONJS = calendarEl.dataset.calendar;
    
    // Parse the JSON string into an array of event objects
    let evenementsJSONJSArray = JSON.parse(evenementsJSONJS);

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
      dateClick: function (info) {
        let nouvelEvenement = {
          title: "nouveau",
          start: info.dateStr,
          allDay: true
        };

        var allEvents = calendar.getEvents();
        var existe = allEvents.some(function (event) {
          return event.title === nouvelEvenement.title &&
            new Date(event.start).toDateString() === new Date(nouvelEvenement.start).toDateString();
        });

        if (!existe) {
          axios.post("/add/evenement", nouvelEvenement)
            .then(function (response) {
              nouvelEvenement.id = response.data.id;
              calendar.addEvent(nouvelEvenement);
            });
        } else {
          console.log("Event already exists, skipping.");
        }
      },

      // Delete availability unpon clicking
      eventClick: function (info) {
        let idToolAvailabilityDelete = info.event.id;
        let nameToolAvailability = info.event.title;
        let startToolAvailability = info.event.start;
        console.log("Event name:", nameToolAvailability);
        console.log("Event start:", startToolAvailability);


        
        
        axios.post("/tool/delete/availability/", { id: idToolAvailabilityDelete })
        .then(function (response) {
          calendar.getEventById(idToolAvailabilityDelete).remove();
          console.log("Event deleted successfully");
        }) .catch(error => {
          console.error("There was an error deleting the event!", error);
        });



        console.log("Event ID to delete:", idToolAvailabilityDelete);
      },
      plugins: [interactionPlugin, dayGridPlugin],
    });

    // Render the calendar
    calendar.render();

  } else {
    console.error("Calendar element or data not found.");
  }
});
