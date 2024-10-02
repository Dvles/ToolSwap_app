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
      eventClick: function (info) {
        let idEvenementEffacer = info.event.id;

        axios.post("/effacer/evenement", { id: idEvenementEffacer })
          .then(function (response) {
            calendar.getEventById(idEvenementEffacer).remove();
          });
      },
      plugins: [interactionPlugin, dayGridPlugin],
    });

    // Render the calendar
    calendar.render();

  } else {
    console.error("Calendar element or data not found.");
  }
});
