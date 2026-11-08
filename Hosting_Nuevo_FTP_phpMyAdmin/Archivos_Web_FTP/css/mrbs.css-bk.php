

/* ------------ GENERAL -----------------------------*/

body {
  font-size: small;
  margin: 0;
  padding: 0;
  color: #0B263B;
  font-family: Arial, 'Arial Unicode MS', Verdana, sans-serif;
  background-color: #f9fbfc;
}

.unsupported_browser body > * {
  display: none;
}

.unsupported_message {
  display: none;
}

.unsupported_browser body .unsupported_message {
  display: block;
}

.current {
  color: #ff0066;  }

.error {
  color: #ff0066;    font-weight: bold;
}

.warning {
  color: #ff0066;  }

.note {
  font-style: italic;
}

input, textarea {
  box-sizing: border-box;
}

input.date,
.js input[type="date"],
input.form-control.input {
  width: 10em;
}

input.form-control.input.flatpickr-mobile {
  width: auto;
}

input.form-control.input {
  text-align: center;
}

input.date {
  text-align: center;
}

button.image {
  background-color: transparent;
  border: 0;
  padding: 0;
}

.contents, .banner {
  padding: 0 1.5rem;
}

.contents {
  float: left;
  width: 100%;
  box-sizing: border-box;
  padding-bottom: 3rem;
}

h1 {
  font-size: x-large;
  clear: both;
}

h2 {
  font-size: large;
  clear: both;
}

.minicalendars {
  padding-top: 0.8rem; }

.minicalendars.formed {
  margin-right: 2em;
  display: none;
}

@media screen and (min-width: 80rem) {

  .minicalendars.formed {
    display: block;
  }
}

.flatpickr-calendar.inline {
  width: auto;
  font-size: 85%;
  margin-bottom: 1rem;
}

.flatpickr-calendar.inline .dayContainer {
  width: calc(7 * 25px);
  min-width: calc(7 * 25px);
  max-width: calc(7 * 25px);
}

.flatpickr-calendar.inline .flatpickr-days {
  width: calc(7 * 25px);
}

.flatpickr-calendar.inline .flatpickr-day {
  max-width: 25px;
  height: 25px;
  line-height: 25px;
}

.flatpickr-months .flatpickr-month {
  height: 40px;
}

.index :not(.simple) + .contents {
  display: -ms-flexbox;
  display: flex;
}

.view_container {
  -ms-flex-positive: 1;
  flex-grow: 1;
  width: 100%;
  overflow-x: auto;
}

img {
  border: 0;
}

a:link {
  color: #0B263B;
  text-decoration: none;
  font-weight: bold;
}

a:visited {
  color: #0B263B;
  text-decoration: none;
  font-weight: bold
}

a:hover {
  color: #0B263B;
  text-decoration: underline;
  font-weight: bold;
}

span.not_allowed {
  cursor: not-allowed;
  display: block;
  width: 100%;
  height: 100%;
}

tr:nth-child(odd) td.new,
.all_rooms tr:nth-child(odd) td {
  background-color: #efefef;
}

.style_weekends tr:nth-child(odd) td.new.weekend,
.style_weekends .all_rooms tr:nth-child(odd) td.weekend {
  background-color: #e4e4e4;
}

tr:nth-child(odd) td.new.holiday,
.all_rooms tr:nth-child(odd) td.holiday {
  background-color: #d8d8d8;
}

.style_weekends tr:nth-child(odd) td.new.weekend.holiday,
.style_weekends .all_rooms tr:nth-child(odd) td.weekend.holiday {
  background-color: #cfcfcf;
}

tr:nth-child(even) td.new,
.all_rooms tr:nth-child(even) td {
  background-color: #ffffff;
}

.style_weekends tr:nth-child(even) td.new.weekend,
.style_weekends .all_rooms tr:nth-child(even) td.weekend {
  background-color: #f4f4f4;
}

tr:nth-child(even) td.new.holiday,
.all_rooms tr:nth-child(even) td.holiday {
  background-color: #e8e8e8;
}

.style_weekends tr:nth-child(even) td.new.weekend.holiday,
.style_weekends .all_rooms tr:nth-child(even) td.weekend.holiday {
  background-color: #dfdfdf;
}

.all_rooms td {
  height: 100%; }

.dwm_main.all_rooms td a {
  display: flex;
  height: 100%;
  padding: 0;
}

.all_rooms td a div {
  box-sizing: border-box;
  min-width: 1px;
  width: 0;
  border-right: 1px dotted  #e4e4e4;
  white-space: nowrap;
  overflow: hidden;
  padding: 0.2em 0;
}

.all_rooms td a div:not(.free)::before {
  content: '\00a0';
}

.all_rooms td a:hover {
  text-decoration: none;
}

.all_rooms td a div:last-child,
.all_rooms td a div.free {
  border-right: 0;
}

td, th {
  vertical-align: top;
}

td form {
  margin: 0;  }

legend {
  font-weight: bold;
  font-size: large;
  font-family: Arial, 'Arial Unicode MS', Verdana, sans-serif;
  color: #0B263B;
}

fieldset {
  margin: 0;
  padding: 0;
  border: 0;
  -webkit-border-radius: 8px;
  -moz-border-radius: 8px;
  border-radius: 8px;
}

fieldset.admin {
  width: 100%; padding: 0 1.0em 1.0em 1.0em;
  border: 1px solid #C3CCD3;
}

fieldset fieldset {
  position: relative;
  clear: left;
  width: 100%;
  padding: 0;
  border: 0;
  margin: 0;
}

fieldset fieldset legend {
  font-size: 0;  }

label:not(.link)::after,
label.link a::after,
.list td:first-child::after {
  content: ':';
}

[lang="fr"] label:not(.link)::after,
[lang="fr"] label.link a::after,
[lang="fr"] .list td:first-child::after  {
  content: '\0000a0:';  }

label:empty::after, .group label::after {
  visibility: hidden;
}

label.no_suffix::after,
[lang="fr"] label.no_suffix::after,
.dataTables_wrapper label::after,
.list td.no_suffix:first-child::after {
  content: '';
}


table.admin_table {
  border-collapse: separate;
  border-spacing: 0;
  border-color: #C3CCD3;
}

.admin_table th, .admin_table td,
table.dataTable thead th, table.dataTable thead td,
table.dataTable tbody th, table.dataTable tbody td {
  box-sizing: border-box;
  vertical-align: middle;
  text-align: left;
  padding: 0.1em 24px 0.1em 0.6em;
  border-style: solid;
  border-width: 0 1px 0 0;
}

.admin_table th:first-child, .admin_table td:first-child,
table.dataTable thead th:first-child, table.dataTable thead td:first-child {
  border-left-width: 1px;
}

.admin_table td, .admin_table th,
table.dataTable thead th, table.dataTable thead td {
  border-color: #C3CCD3;
}

.admin_table th:first-child,
table.dataTable thead th:first-child, table.dataTable thead td:first-child {
  border-left-color: #0b2147;
}

.admin_table th:last-child {
  border-right-color: #0b2147;
}

.admin_table.DTFC_Cloned th:last-child {
  border-right-color: #C3CCD3;
}

.admin_table th,
table.dataTable thead .sorting,
table.dataTable thead .sorting_asc,
table.dataTable thead .sorting_desc {
  color: #ffffff;
  background-color: #0b2147;
}

.admin_table td.action {
  text-align: center;
}

.admin_table td.action div {
  display: inline-block;
}

.admin_table td.action div div {
  display: table-cell;
}

body:not(.js) table.display {
  width: 100%;
}

table.display tbody tr:nth-child(2n) {
  background-color: #ffffff;
}

table.display tbody tr:nth-child(2n+1) {
  background-color: #e2e4ff;
}

table.display th, table.display td {
  height: 2em;
  white-space: nowrap;
  overflow: hidden;
}

table.display th {
  padding: 3px 24px 3px 8px;
}

table.display span {
  display: none;
}

table.display span.normal {
  display: inline;
}

select.room_area_select,
nav.location .select2-container {
  margin: 0 0.5em;
}

.none {
  display: none;
}

.js .js_none {
  display: none;
}

.js .js_hidden {
  visibility: hidden;
}

h2.date, span.timezone {
  display: inline-block;
  width: 100%;
  text-align: center;
  margin-bottom: 0.1em;
}

span.timezone {
  opacity: 0.8;
  font-size: smaller;
}

nav.main_calendar {
  display: -ms-flexbox;
  display: flex;
  -ms-flex-align: center;
  align-items: center;
  -ms-flex-wrap: wrap;
  flex-wrap: wrap;
  width: 100%;
  margin-top: 0.8rem;  }

nav.main_calendar > nav {
  display: -ms-flexbox;
  display: flex;
  -ms-flex: 1;
  flex: 1;
  -ms-flex-pack: center;
  justify-content: center;
}

nav.main_calendar.wrapped nav.location {
  -ms-flex-wrap: wrap;
  flex-wrap: wrap;
}

nav.main_calendar.wrapped nav.location {
  flex-basis: 100%;
}

nav.main_calendar.wrapped:nth-of-type(1) nav.location {
  order: -1;
}

nav.main_calendar.wrapped:nth-of-type(1) nav.location > * {
  margin-bottom: 1em;
}

nav.main_calendar.wrapped:nth-of-type(2) nav.location {
  order: 1;
}

nav.main_calendar.wrapped:nth-of-type(2) nav.location > * {
  margin-top: 1em;
}

nav.main_calendar > nav:first-child {
  -ms-flex-pack: start;
  justify-content: flex-start;
}

nav.main_calendar > nav:last-child {
  -ms-flex-pack: end;
  justify-content: flex-end;
}

nav.view div.container {
  display: inline-grid;
  grid-template-columns: 1fr 1fr 1fr;
}

nav.view a, nav.arrow a {
  background: linear-gradient(#eeeeee, #cccccc);
  border-right: thin solid #f9fbfc;
  cursor: pointer;
  line-height: 1.8em;
  font-weight: normal;
  text-align: center;
}

nav.view a:first-child, nav.arrow a:first-child {
  border-top-left-radius: 5px;
  border-bottom-left-radius: 5px;
}

nav.view a:last-child, nav.arrow a:last-child {
  border-right: 0;
  border-top-right-radius: 5px;
  border-bottom-right-radius: 5px;
}

nav.view a {
  padding: 0.2em 0.5em;
}

nav.arrow a {
  padding: 0.2em 1.1em;
}

nav a.selected,
nav.view a:hover,
nav.view a:focus,
nav.arrow a:hover,
nav.arrow a:focus {
  background: #0b2147;
  box-shadow: inset 1px 1px darkblue;
  color: #ffffff;
  text-decoration: none;
}

nav a.prev::before {
  content: '\00276e';  /* HEAVY LEFT-POINTING ANGLE QUOTATION MARK ORNAMENT */
}

nav a.next::after {
  content: '\00276f';  /* HEAVY RIGHT-POINTING ANGLE QUOTATION MARK ORNAMENT */
}


/* ------------ ADMIN.PHP ---------------------------*/

.form_admin fieldset {
  border: 1px solid #C3CCD3;
}

.admin h2 {
  clear: left
}

div#area_form, div#room_form {
  width: 100%;
  float: left;
  padding: 0 0 2em 0;
}

div#div_custom_html {
  float: left;
  padding: 0 0 3em 1em;
}

#area_form form {
  width: 100%;
  float: left;
  margin-right: 1em
}

#area_form label[for="area_select"] {
  display: block;
  float: left;
  font-weight: bold;
  margin-right: 1em;
}

.areaChangeForm div {
  float: left;
}

.roomChangeForm select, .areaChangeForm select {
  font-size: larger;
}

.roomChangeForm input, .areaChangeForm input {
  float: left;
  margin: -0.2em 0.5em 0 0;
}

.roomChangeForm input.button, .areaChangeForm button.image {
  display: block;
  float: left;
  margin: 0 0.7em;
}


/* ------------ INDEX.PHP ------------------*/

.date_nav {
  float: left;
  width: 100%;
  margin-top: 0.5em;
  margin-bottom: 0.5em;
  font-weight: bold
}

.date_nav a {
  display: block;
  width: 33%;
}

.date_before {
  float: left;
  text-align: left;
}

.date_now {
  float: left;
  text-align: center;
}

.date_after {
  float: right;
  text-align: right;
}

.date_before::before {
  content: '<<\0000a0';
}

.date_after::after {
  content: '\0000a0>>';
}

.table_container {
  overflow: auto;
  position: relative;
    max-height: calc(100vh - 4em);
    max-height: max(calc(100vh - 4em), 8em);
  margin: 1em 0;
}

div.timeline {
  background-color: #0b2147;
  position: absolute;
  width: 0;
    height: 2px;   transform: translate(0, -50%);
  z-index: 90;
  pointer-events: none;
}

div.timeline.times_along_top {
  height: 0;
    width: 2px;   transform: translate(-50%, 0);
}

table.dwm_main {
  float: left;
  clear: both;
  width: 100%;
  height: 100%;
  border-spacing: 0;
  border-collapse: separate;
  border-color: #dddddd;
  border-width: 0px;
  border-style: solid;
  border-radius: 5px;
}

.dwm_main th, .dwm_main td {
  min-height: 1.5em;
  line-height: 1.5em;
}

.dwm_main tbody th {
  text-align: left;
}

.dwm_main td {
  position: relative;
  padding: 0;
  border-bottom: 0;
  border-right: 0;
}

.dwm_main td,
.dwm_main tbody td + th {
  border-right: 1px solid #e4e4e4;
}

.series a::before {
  content: '\0021bb';  /* CLOCKWISE OPEN CIRCLE ARROW */
  margin-right: 0.5em;
}

.awaiting_approval a::before {
  content: '?';
  margin-right: 0.5em;
}

.awaiting_approval.series a:before {
  content: '?\002009\0021bb';  /* THIN SPACE, CLOCKWISE OPEN CIRCLE ARROW */
}

.awaiting_approval {
  opacity: 0.6
}

.private {
  opacity: 0.6;
  font-style: italic;
}

.tentative {
  opacity: 0.6;
}

.tentative a {
  font-weight: normal;
}

.capacity::before {
  content: ' (';
}

.capacity::after {
  content: ')';
}

.capacity.zero {
  display: none;
}

.dwm_main thead th,
.dwm_main tfoot th {
  font-size: small;
  font-weight: normal;
  vertical-align: top;
  padding: 0.2em;
  color: #0B263B;
  background-color: #ffffff;
  background-clip: padding-box; }

.style_weekends .dwm_main thead th.weekend,
.style_weekends .dwm_main tfoot th.weekend {
  background-color: #f4f4f4}

.dwm_main thead th.holiday,
.dwm_main tfoot th.holiday {
  background-color: #e8e8e8}

.style_weekends .dwm_main thead th.weekend.holiday,
.style_weekends .dwm_main tfoot th.weekend.holiday {
  background-color: #dfdfdf}

.dwm_main th,
.dwm_main td {
  border-right: 1px solid #dddddd;
}

.dwm_main thead tr:last-child th {
  border-bottom: 1px solid #0b2147;
}

.dwm_main tfoot tr:first-child th {
  border-top: 1px solid #0b2147;
}

.dwm_main thead tr:first-child th:first-child {
  border-top-left-radius: 5px;
}

.dwm_main thead tr:first-child th:last-child {
  border-top-right-radius: 5px;
}

.dwm_main tfoot tr:last-child th:first-child,
.dwm_main thead + tbody tr:last-child th:first-child {
  border-bottom-left-radius: 5px;
}

.dwm_main tfoot tr:last-child th:last-child,
.dwm_main thead + tbody tr:last-child > *:last-child {
  border-bottom-right-radius: 5px;
}


.dwm_main a,
.dwm_main .booked span.saving {
  display: block;
  height: 100%;
  width: 100%;
  min-height: inherit;
  word-break: break-all;
  word-break: break-word;   hyphens: auto;
}

.dwm_main .booked a,
.dwm_main .booking,
.dwm_main .booked span.saving {
  position: absolute;
  top: 0;
  left: 0;
  z-index: 50;
  overflow: hidden;
}

.dwm_main.times-along-top .booked.multiply a {
  max-height: 1.5em;
  box-sizing: content-box;
}

@media all and (-ms-high-contrast: none), all and (-ms-high-contrast: active)
{
  .dwm_main .booking {
    position: relative;
  }
}

.dwm_main .booking {
  width: 100%;
  height: 100%;   min-height: 1.5em;
}

.dwm_main .booked.multiply a,
.dwm_main .booked.multiply .booking {
  position: relative;
}

.dwm_main .booked.multiply .booking {
  height: 1.5em; }

.dwm_main .booked a {
  box-sizing: border-box;
}

.dwm_main .booked a,
.all_rooms td a div:not(.free) {
  border-bottom: 1px solid #e4e4e4;
}

.dwm_main .booked a.saving {
  opacity: 0.4;
  color: transparent;
  pointer-events: none;
}

.dwm_main .booked span.saving {
  font-weight: bold;
}

.dwm_main .booked span.saving::after,
.loading::after {
  content: '\002026'; }

.dwm_main .booked span.saving,
.dwm_main .booked span.saving::after {
  z-index: 60;
  animation-name: pulsate;
  animation-duration: 2s;
  animation-timing-function: ease-in-out;
  animation-iteration-count: infinite;
}

@keyframes pulsate {
  from {
    opacity: 0;
  }

  50% {
    opacity: 1;
  }

  to {
    opacity: 0;
  }
}

.dwm_main tbody a {
  padding: 0.2em;
  box-sizing: border-box;
}

.dwm_main th a {
  text-decoration: none;
  font-weight: normal;
}

.dwm_main th a:link {
  color: #0B263B;
}

.dwm_main th a:visited {
  color: #0B263B;
}

.dwm_main th a:hover {
  color: #0B263B;
  text-decoration:underline;
}

.dwm_main thead th.first_last,
.dwm_main tfoot th.first_last {
  width: 1px;
}

.dwm_main#week_main thead th.first_last,
.dwm_main#week_main tfoot th.first_last {
  vertical-align: bottom;
}

.dwm_main td.invalid {
  background-color: #d1d9de;
}

.dwm_main#month_main:not(.all_rooms) tbody tr:not(:first-child) td {
  border-top:  1px solid #e4e4e4;
}

.dwm_main#month_main:not(.all_rooms) td {
  background-color: #ffffff;
}

.style_weekends .dwm_main#month_main:not(.all_rooms) td.weekend {
  background-color: #f4f4f4;
}

.dwm_main#month_main:not(.all_rooms) td.holiday {
  background-color: #e8e8e8;
}

.style_weekends .dwm_main#month_main:not(.all_rooms) td.weekend.holiday {
  background-color: #dfdfdf;
}

.dwm_main#month_main td.invalid {
  background-color: #d1d9de;
}

.dwm_main#month_main:not(.all_rooms) a {
  height: 100%;
  width: 100%;
  padding: 0 2px 0 2px;
}

td.new a, a.new_booking {
  font-size: medium;
  text-align: center;
}

td.new img, .new_booking img {
  margin: auto;
  padding: 4px 0 2px 0;
}

.resizable-helper {
  outline: 2px solid #666666;
  outline-offset: -2px;
  position: absolute;
  top: 0;
  left: 0;
  z-index: 100 !important;
}



div.cell_container {
  position: relative;
  float: left;
  width: 100%;
  height: 100px;
}

#month_main a.new_booking {
  position: absolute;
  top: 0;
  left: 0;
  z-index: 10;  }

div.cell_header {
  position: relative;
  width: 100%;
  z-index: 20;    min-height: 20%;
  height: 20%;
  max-height: 20%;
  overflow: hidden;
}

#month_main div.cell_header a {
  display: block;
  width: auto;
  float: left;
}

#month_main div.cell_header a.monthday {
  font-size: medium;
}

#month_main div.cell_header a.week_number {
  opacity: 0.5;
  padding: 2px 4px 0 4px;
}

.view_week_number #month_main div.cell_header a.monthday {
  text-align: center;
}

.view_week_number #month_main div.cell_header a.week_number {
  position: absolute;
}

div.booking_list {
  position: relative;
  z-index: 20;    max-height: 80%;
  font-size: x-small;
  overflow: auto;
}

div.description, div.slot {
  width: 50%;
}

div.both {
  width: 100%;
}

.booking_list div {
  float: left;
  min-height: 1.3em;
  line-height: 1.3em;
  overflow: hidden;
}

  .booking_list div {
    height: 1.3em;
    max-height: 1.3em;
  }
  

.booking_list a {
  font-size: x-small;
}


.A {background: #ffff99}
.full.A {background: linear-gradient(to right bottom, #ffff99 calc(50% - 1.5px), #ffffff calc(50% - 0.5px) calc(50% + 0.5px), #ffff99 calc(50% + 1.5px))}
.spaces.A {background: linear-gradient(to right bottom, #ffffff calc(50% - 0.5px), #ffff99 calc(50% + 0.5px))}
.B {background: #99cccc}
.full.B {background: linear-gradient(to right bottom, #99cccc calc(50% - 1.5px), #ffffff calc(50% - 0.5px) calc(50% + 0.5px), #99cccc calc(50% + 1.5px))}
.spaces.B {background: linear-gradient(to right bottom, #ffffff calc(50% - 0.5px), #99cccc calc(50% + 0.5px))}
.C {background: #ffffcd}
.full.C {background: linear-gradient(to right bottom, #ffffcd calc(50% - 1.5px), #ffffff calc(50% - 0.5px) calc(50% + 0.5px), #ffffcd calc(50% + 1.5px))}
.spaces.C {background: linear-gradient(to right bottom, #ffffff calc(50% - 0.5px), #ffffcd calc(50% + 0.5px))}
.D {background: #cde6e6}
.full.D {background: linear-gradient(to right bottom, #cde6e6 calc(50% - 1.5px), #ffffff calc(50% - 0.5px) calc(50% + 0.5px), #cde6e6 calc(50% + 1.5px))}
.spaces.D {background: linear-gradient(to right bottom, #ffffff calc(50% - 0.5px), #cde6e6 calc(50% + 0.5px))}
.E {background: #6dd9c4}
.full.E {background: linear-gradient(to right bottom, #6dd9c4 calc(50% - 1.5px), #ffffff calc(50% - 0.5px) calc(50% + 0.5px), #6dd9c4 calc(50% + 1.5px))}
.spaces.E {background: linear-gradient(to right bottom, #ffffff calc(50% - 0.5px), #6dd9c4 calc(50% + 0.5px))}
.F {background: #82adad}
.full.F {background: linear-gradient(to right bottom, #82adad calc(50% - 1.5px), #ffffff calc(50% - 0.5px) calc(50% + 0.5px), #82adad calc(50% + 1.5px))}
.spaces.F {background: linear-gradient(to right bottom, #ffffff calc(50% - 0.5px), #82adad calc(50% + 0.5px))}
.G {background: #ccffcc}
.full.G {background: linear-gradient(to right bottom, #ccffcc calc(50% - 1.5px), #ffffff calc(50% - 0.5px) calc(50% + 0.5px), #ccffcc calc(50% + 1.5px))}
.spaces.G {background: linear-gradient(to right bottom, #ffffff calc(50% - 0.5px), #ccffcc calc(50% + 0.5px))}
.H {background: #2e64f7}
.full.H {background: linear-gradient(to right bottom, #2e64f7 calc(50% - 1.5px), #ffffff calc(50% - 0.5px) calc(50% + 0.5px), #2e64f7 calc(50% + 1.5px))}
.spaces.H {background: linear-gradient(to right bottom, #ffffff calc(50% - 0.5px), #2e64f7 calc(50% + 0.5px))}
.I {background: #99cc66}
.full.I {background: linear-gradient(to right bottom, #99cc66 calc(50% - 1.5px), #ffffff calc(50% - 0.5px) calc(50% + 0.5px), #99cc66 calc(50% + 1.5px))}
.spaces.I {background: linear-gradient(to right bottom, #ffffff calc(50% - 0.5px), #99cc66 calc(50% + 0.5px))}
.J {background: #E84438}
.full.J {background: linear-gradient(to right bottom, #E84438 calc(50% - 1.5px), #ffffff calc(50% - 0.5px) calc(50% + 0.5px), #E84438 calc(50% + 1.5px))}
.spaces.J {background: linear-gradient(to right bottom, #ffffff calc(50% - 0.5px), #E84438 calc(50% + 0.5px))}

.full a,
.spaces a {
  background: transparent;
}

tbody tr:nth-child(odd) .spaces {
  box-shadow: 0 1px 0 #efefef inset;
}

.private_type {
  background: #d1d9de;
}

.dwm_main thead th,
.dwm_main th:first-child {
  position: -webkit-sticky;
  position: sticky;
  z-index: 600;
}

.dwm_main thead th {
  top: 0;
}

.dwm_main th:first-child {
  left: 0;
}

.dwm_main thead th:first-child {
  z-index: 610;
}

.hidden_day {
  display: none;
}

td.hidden_day {
  background-color: #d1d9de;
  font-size: medium;
  font-weight: bold;
}

tr.row_highlight td.new {
  background-color: #0b2147;
}

th {
  white-space: nowrap;
  vertical-align: middle;
}

tbody tr:nth-child(odd) th {
  background-color: #efefef;
}

.style_weekends tbody tr:nth-child(odd) th.weekend {
  background-color: #e4e4e4;
}

tbody tr:nth-child(odd) th.holiday {
  background-color: #d8d8d8;
}

.style_weekends tbody tr:nth-child(odd) th.weekend.holiday {
  background-color: #cfcfcf;
}

tbody tr:nth-child(even) th {
  background-color: #ffffff;
}

.style_weekends tbody tr:nth-child(even) th.weekend {
  background-color: #f4f4f4;
}

tbody tr:nth-child(even) th.holiday {
  background-color: #e8e8e8;
}

.style_weekends tbody tr:nth-child(even) th.weekend.holiday {
  background-color: #dfdfdf;
}

tbody th a {
  display: inline-block;
  text-decoration: none;
  font-weight: normal
}

tbody th a:link {
  color: #0B263B;
}

tbody th a:visited {
  color: #0B263B;
}

tbody th a:hover {
  color: #0B263B;
  text-decoration: underline;
}

.dwm_main td:hover.new, .dwm_main td.new_hover {
  background-color: #0b2147;
}

.dwm_main tbody tr:hover th {
  background-color: #0b2147;
}

.dwm_main tbody tr:hover th a {
  color: #ffffff;
}

.dwm_main#month_main td:hover.valid,
.dwm_main#month_main td.valid_hover {
  background-color: #0b2147;
}

.dwm_main.resizing tbody tr:nth-child(odd) td:hover.new {
  background-color: #efefef;
}

[style-wekends=true] .dwm_main.resizing tbody tr:nth-child(odd) td.weekend:hover.new {
  background-color: #e4e4e4;
}

.dwm_main.resizing tbody tr:nth-child(odd) td.holiday:hover.new {
  background-color: #d8d8d8;
}

[style-wekends=true] .dwm_main.resizing tbody tr:nth-child(odd) td.weekend.holiday:hover.new {
  background-color: #cfcfcf;
}

.dwm_main.resizing tbody tr:nth-child(even) td:hover.new {
  background-color: #ffffff;
}

[style-wekends=true] .dwm_main.resizing tbody tr:nth-child(even) td.weekend:hover.new {
  background-color: #f4f4f4;
}

.dwm_main.resizing tbody tr:nth-child(even) td.holiday:hover.new {
  background-color: #e8e8e8;
}

[style-wekends=true] .dwm_main.resizing tbody tr:nth-child(even) td.weekend.holiday:hover.new {
  background-color: #dfdfdf;
}

.dwm_main.resizing tbody tr:hover th {
  background-color: #0b2147;
  color: #0B263B;
}

.resizing tbody th a:hover {
  text-decoration: none;
}

.dwm_main.resizing tbody tr:hover th a:link {
  color: #0B263B;
}

.dwm_main.resizing tbody tr:hover th a:visited {
  color: #0B263B;
}

.dwm_main.resizing tbody tr th.selected {
  background-color: #0b2147;
}

.dwm_main.resizing tbody tr:hover th.selected,
.dwm_main.resizing tbody tr th.selected a:link,
.dwm_main.resizing tbody tr th.selected a:visited {
  color: #ffffff;
}


.dwm_main .ui-resizable-n {
  top: -1px;
}

.dwm_main .ui-resizable-e {
  right: -1px;
}

.dwm_main .ui-resizable-s {
  bottom: -1px;
}

.dwm_main .ui-resizable-w {
  left: -1px;
}

.dwm_main .ui-resizable-se {
  bottom: 0;
  right: 0;
}

.dwm_main .ui-resizable-sw {
  bottom: -2px;
  left: -1px;
}

.dwm_main .ui-resizable-ne {
  top: -2px;
  right: -1px;
}

.dwm_main .ui-resizable-nw {
  top: -2px;
  left: -1px;
}


div.div_select {
  position: absolute;
  border: 0;
  opacity: 0.2;
  background-color: #0b2147;
}

div.div_select.outside {
  background-color: transparent;
}


/* ------------ DEL.PHP -----------------------------*/
div#del_room_confirm {
  text-align: center;
  padding-bottom: 3em;
}

#del_room_confirm p, #del_room_confirm input[type="submit"] {
  font-size: large;
  font-weight: bold;
}

#del_room_confirm form {
  display: inline-block;
  margin: 1em 2em;
}



/* ------ EDIT_AREA.PHP AND EDIT_ROOM.PHP ----------*/

#book_ahead_periods_note span {
  display: block;
  float: left;
  width: 24em;
  margin: 0 0 1em 1em;
  font-style: italic;
}

div#div_custom_html {
  margin-top: 2em;
}

.delete_period, #period_settings button {
  display: none;
}

.js .delete_period {
  display: inline-block;
  visibility: hidden;   padding: 0 1em;
  opacity: 0.7;
}

.delete_period::after {
  content: '\002718';    color: red;
}

.delete_period:hover {
  cursor: pointer;
  opacity: 1;
  font-weight: bold;
}

.js #period_settings button {
  display: inline-block;
  margin-left: 1em;
}

.buttons input {
  margin-top: 2em;
}



.standard {
  float:left;
  margin-top: 2.0em;
}

.standard fieldset {
  display: table;
  float: left;
  clear: left;
  width: auto;
  border-spacing: 0 0.75em;
  border-collapse: separate;
  padding: 1em 1em 1em 0;
}

.standard#logon fieldset {
  padding-bottom: 0;
}

.standard fieldset > div {
  display: table-row;
}

.standard fieldset > div > *:not(.none) {
  display: table-cell;
  vertical-align: middle;
}

.standard fieldset .multiline label {
  vertical-align: top;
}

.standard fieldset .field_text_area label {
  vertical-align: top;
  padding-top: 0.2em;
}

.standard fieldset > div > div > * {
  float: left;
  margin-top: 0;
}

.standard ul {
  clear: left;
}

.reset_password .standard p {
  float: left;
  clear: left;
}

.standard fieldset fieldset {
  padding: 1em 0;
}

.standard fieldset fieldset legend{
  font-size: small;
  font-style: italic;
  font-weight: normal;
}

.standard fieldset fieldset fieldset legend {
  padding-left: 2em;
}

.standard fieldset > div > label {
  font-weight: bold;
  padding-left: 2em;
  padding-right: 1em;
  text-align: right;
}

.standard fieldset > div > div {
  text-align: left;
}

.standard div.group {
  display: inline-block;
  float: left;
}

.standard div.group.long label {
  float: left;
  clear: left;
  margin-bottom: 0.5em;
}

.standard input[type="text"]:not(.date):not(.form-control),
.standard input[type="email"],
.standard input[type="password"],
.standard input[type="search"],
.standard input[type="url"],
.standard textarea {
  width: 17rem;  }

.standard input[type="text"].short {
  width: 4em;
}

.standard input[type="number"] {
  width: 4em;
}

.standard input[type="number"][step="0.01"] {
  width: 6em;
}

.standard input[type="radio"], .standard input[type="checkbox"] {
  vertical-align: middle;
  margin: -0.17em 0.4em 0 0;
}

.standard input, .standard input.enabler, .standard select {
  margin-right: 1em;
}

.standard textarea {
  height: 6em;
}

.standard .group label {
  margin-right: 0.5em;
}

@media (max-width: 30rem) {
  .standard fieldset {
    display: block;
  }

  .standard fieldset > div {
    display: block;
    float: left;
    clear: left;
  }

  .standard fieldset div > * {
    margin-bottom: 1em;
  }

  .standard fieldset > div > *:not(label):not(.none) {
    display: inline-block;
    vertical-align: middle;
  }

  .standard fieldset > div > label {
    display: block;
    text-align: left;
    padding-left: 0;
    padding-right: 0;
    margin-bottom: 0.2em;
  }

  .standard fieldset.rep_type_details {
    padding-bottom: 0;
    margin-bottom: 0;
  }

  .standard input#rep_interval {
    float: left;
  }

  .standard div#rep_type div.long {
    border-right: 0;
  }

  .standard .submit_buttons > * {
    float: left;
  }
}


.standard fieldset.max_limits > div > label {
  vertical-align: top;
  margin-bottom: 0.5em;
}

.max_limits div:first-of-type span {
  display: inline-block;
  width: 50%;
}

.max_limits div div div {
  float: left;
  margin-bottom: 0.5em;
}

.max_limits div:first-of-type span {
  white-space: normal;
  font-style: italic;
}

.max_limits div div {
  white-space: nowrap;
}

.max_limits input {
  display: inline-block;
}

.max_limits input[type="checkbox"] {
  margin-right: 1em;
}

div#rep_type div.long{
  border-right: 1px solid #C3CCD3;
  padding-right: 1em;
}

fieldset.rep_type_details fieldset {
  padding-top: 0
}

#rep_monthly input[type="radio"] {
  margin-left: 2em;
}

.standard fieldset fieldset.rep_type_details {
  padding-top: 0;
  clear: none;
}

fieldset#rep_info,
fieldset#booking_controls {
  border-top: 1px solid #C3CCD3;
  border-radius: 0;
  padding-top: 0.7em;
}

span#interval_units {
  display: inline-block;
}

.edit_entry span#end_time_error {
  display: block;
  float: left;
  margin-left: 2em;
  font-weight: normal;
}

div#checks {
  white-space: nowrap;
  letter-spacing: 0.9em;
  margin-left: 3em;
}

div#checks span {
  cursor: pointer;
}

.good::after {
  content: '\002714';    color: green;
}

.notice::after {
  content: '!';
  font-weight: bold;
  color: #ff5722;
}

.bad::after {
  content: '\002718';    color: red;
}


/* ------------ EDIT_ENTRY_HANDLER.PHP ------------------*/

.edit_entry_handler div#submit_buttons {
  float: left;
}

.edit_entry_handler #submit_buttons form input {
  float: left;
  margin: 1em 2em 1em 0;
}


/* ------------ EDIT_USERS.PHP ------------------*/

div#user_list {
  padding: 2em 0;
}

form#add_new_user {
  margin-left: 1em;
}

#users_table td {
  text-align: right;
}

#users_table td div.string {
  text-align: left;
}


/* ------------ FUNCTIONS.INC -------------------*/

.banner {
  display: -ms-flexbox;
  display: flex;
  -ms-flex-wrap: wrap;
  flex-wrap: wrap;
  -ms-flex-direction: row;
  flex-direction: row;
  -ms-flex-pack: start;
  justify-content: flex-start;
  background-color: #0b2147;
  color: #ffffff;
  border-color: #f9fbfc;
  border-width: 0px;
  border-style: solid;
}

.banner .logo {
  display: -ms-flexbox;
  display: flex;
  -ms-flex-align: center;
  align-items: center;
  -ms-flex-pack: start;
  justify-content: flex-start;
}

.banner .logo img {
  display: block;
  margin: 1em 2em 1em 0;
  max-width: 250px;
  height: auto;
}

.banner .company {
  display: -ms-flexbox;
  display: flex;
  -ms-flex-direction: column;
  flex-direction: column;
  -ms-flex-align: center;
  align-items: center;
  -ms-flex-pack: center;
  justify-content: center;
  font-size: large;
  padding: 0.5rem 2rem 0.5rem 0;
  margin-right: 2rem;
  white-space: nowrap;
}

.banner .company > * {
  display: -ms-flexbox;
}

.banner a:link, .banner a:visited, .banner a:hover {
  text-decoration: none;
  font-weight: normal;
}

.banner a:link, nav.logon input {
  color: #ffffff;
}

.banner a:visited {
  color: #ffffff;
}

.banner a:hover {
  color: #ffffff;
}

.banner nav.container {
  width: 50%;
  -ms-flex-positive: 1;
  flex-grow: 1;
  display: -ms-flexbox;
  display: flex;
  -ms-flex-direction: row-reverse;
  flex-direction: row-reverse;
  justify-content: space-between;
  align-items: stretch;
  -ms-flex-wrap: wrap;
  flex-wrap: wrap;
  padding: 0.75em 0;
}

.banner nav.container > nav {
  display: -ms-flexbox;
  display: flex;
  -ms-flex-direction: row;
  flex-direction: row;
  -ms-flex-align: stretch;
  align-items: stretch;
  justify-content: flex-end;
  padding: 0.3em 0;
}

.banner nav.container > nav > nav {
  -ms-flex-align: center;
  align-items: center;
  justify-content: right;
}

.banner nav.container > nav:first-child {
  -ms-flex-wrap: wrap-reverse;
  flex-wrap: wrap-reverse;
}

.banner nav.container > nav:last-child > * {
  display: -ms-flexbox;
  display: flex;
  -ms-flex-align: center;
  align-items: center;
}

nav.menu {
  display: -ms-flexbox;
  display: flex;
  flex-wrap: wrap;
}

nav.logon {
  display: -ms-flexbox;
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  -ms-flex-align: center;
  align-items: center;
}

nav.logon input {
  background: none;
  border: none;
}

.banner nav a,
nav.logon input,
nav.logon span {
  display: inline-block;
  text-align: center;
  padding: 0.3rem 0.75rem 0.3rem 0.75rem;
  margin-right: -0.75rem;
  margin-left: 0.75rem;
  line-height: 1.5em;
  border-radius: 0.8em;
}

nav.logon a.notification {
  padding-left: 0.8rem;
  padding-right: 0.8rem;
  margin-left: 1.5rem;
}

.banner a.attention {
  background-color: darkorange;
}

.banner nav a:hover,
nav.logon input:hover {
  background-color: darkblue;
  color: #ffffff;
}

#form_nav {
  padding-right: 1rem;
  margin-right: 1rem;
}

input.link[type="submit"] {
  display: inline;
  border: none;
  background: none;
  cursor: pointer;
  font-weight: bold;
  padding: 0;
}

form#show_my_entries input.link[type="submit"] {
  color: #ffffff;
  padding: 0.3em 0;
  font-weight: normal;
}

@media (max-width: 30rem) {
  .banner .logo {
    display: none;
  }

  .banner .company {
    align-items: flex-start;
  }

  .banner .company_more_info {
    display: none;
  }

    .index #form_nav input[type="submit"] {
    display: none;
  }
}


.color_key {
  display: -ms-flexbox;
  display: flex;
  -ms-flex-wrap: wrap;
  flex-wrap: wrap;
}

.color_key {
  display: inline-grid;
  grid-template-columns: repeat(auto-fill, minmax(20ch, 1fr));
  width: 100%;
  margin-top: 1em;
}

.color_key > div {
  width: 12em;
  color: #0B263B;
  word-wrap: break-word;
  padding: 0.3em;
  margin: -1px 0 0 -1px;   font-weight: bold;
  border: 1px solid #ffffff}

.A, .color_key > div.A {
    background: #9cf59c;
    color: #0B263B;
}

.A:link, .A:visited {
    color: #0B263B;
    text-decoration: none;
    font-weight: bold;
}

.B, .color_key > div.B {
    background: #A4D5FF;
    color: #0B263B;
}

.B:link, .B:visited {
    color: #0B263B;
    text-decoration: none;
    font-weight: bold;
}

.C, .color_key > div.C {
    background: #CCC0DA;
    color: #0B263B;
}

.C:link, .C:visited {
    color: #0B263B;
    text-decoration: none;
    font-weight: bold;
}

.D, .color_key > div.D {
    background: #F5BA70;
    color: #0B263B;
}

.D:link, .D:visited {
    color: #0B263B;
    text-decoration: none;
    font-weight: bold;
}

.E, .color_key > div.E {
    background: #AFAFAF;
    color: #0B263B;
}

.E:link, .E:visited {
    color: #0B263B;
    text-decoration: none;
    font-weight: bold;
}

.F, .color_key > div.F {
    background: #F39ACD;
    color: #0B263B;
    
}

.F:link, .F:visited {
    color: #0B263B;
    text-decoration: none;
    font-weight: bold;
}

.G, .color_key > div.G {
    background: #FAE561;
    color: #0B263B;
}

.G:link, .G:visited {
    color: #0B263B;
    text-decoration: none;
    font-weight: bold;
}

.H, .color_key > div.H {
    background: #3067F7;
    color: #0B263B;
    
}

.H:link, .H:visited {
    color: #0B263B;
    text-decoration: none;
    font-weight: bold;
}

.I, .color_key > div.I {
    background: #71F0EF;
    color: #0B263B;
}

.I:link, .I:visited {
    color: #0B263B;
    text-decoration: none;
    font-weight: bold;
}

.J, .color_key > div.J {
    background: #E84438;
    color: #0B263B;
}

.J:link, .J:visited {
    color: #0B263B;
    text-decoration: none;
    font-weight: bold;
}

.K, .color_key > div.K {
    background: #ff9004;
    color: #0B263B;
}

.K:link, .K:visited {
    color: #0B263B;
    text-decoration: none;
    font-weight: bold;
}

.L, .color_key > div.L {
    background: #00a7d1;
    color: #0B263B;
}

.L:link, .L:visited {
    color: #0B263B;
    text-decoration: none;
    font-weight: bold;
}

@supports (display: grid) {
  .color_key > div {
    width: auto;
  }
}



header input[type="search"] {
  width: 10em;
}

.banner .outstanding a {
  color: #FFF36C;
}


/* ------------ HELP.PHP ------------------------*/

table.details {
  border-spacing: 0;
  border-collapse: collapse;
  margin-bottom: 1.5em;
}

table.details:first-child {
  margin-bottom: 0;
}

table.details.has_caption {
  margin-left: 2em;
}

.details caption {
  text-align: left;
  font-weight: bold;
  margin-left: -2em;
  margin-bottom: 0.2em;
}

.details td {
  padding: 0 1.0em 0 0;
}

.details td:first-child {
  text-align: right;
  white-space: nowrap;
}


/* ------------ IMPORT.PHP ------------------------*/

div.problem_report {
  border-bottom: 1px solid #C3CCD3;
  margin-top: 1em;
}


/* ------------ MINCALS.PHP ---------------------*/

table.minicalendar {
  border-spacing: 0;
  border-collapse: collapse;
}

.minicalendar th {
  min-width: 2.0em;
  text-align: center;
  font-weight: normal;
  background-color: transparent;
}

.minicalendar thead tr:first-child th {
  text-align: center;
  vertical-align: middle;
  line-height: 1.5em;
}

.minicalendar thead tr:first-child th:first-child {
  text-align: left;
}

.minicalendar thead tr:first-child th:last-child {
  text-align: right;
}

.minicalendar td {
  text-align: center;
  font-size: x-small;
}

.minicalendar a.arrow {
  display: block;
  width: 100%;
  height: 100%;
  text-align: center;
}

.minicalendar td > * {
  display: block;
  width: 2em;
  height: 2em;
  line-height: 2em;
  margin: auto;
  border-radius: 50%;
}

.minicalendar td.today a,
.minicalendar td a:hover {
  background-color: #bdd4de;
  color: #0B263B}

.minicalendar .view {
  background-color: #2b3a42;
}

.minicalendar .hidden {
  opacity: 0.7
}

.minicalendar a.current {
  font-weight: bold;
  color: #ff0066;
}


/* ------------ PENDING.PHP ------------------*/

#pending_list form {
  display: inline-block;
}

#pending_list td.table_container, #pending_list td.sub_table {
  padding: 0;
  border: 0;
  margin: 0;
}

#pending_list .control {
  padding-left: 0;
  padding-right: 0;
  text-align: center;
  color: #0B263B;
}

.js #pending_list td.control {
  background-color: #FFF36C;
}

#pending_list td:first-child {
  width: 1.2em;
}

#pending_list #pending_table td.sub_table {
  width: auto;
}

table.admin_table.sub {
  border-right-width: 0;
}

table.sub th {
  background-color: #788D9C;
}

.js .admin_table table.sub th:first-child {
  background-color: #FFF36C;
  border-left-color: #C3CCD3;
}

#pending_list form {
  margin: 2px 4px;
}


/* ------------ REPORT.PHP ----------------------*/

div#div_summary {
  padding-top: 3em;
}

#div_summary table {
  border-spacing: 1px;
  border-collapse: collapse;
  border-color: #0B263B;
  border-style: solid;
  border-top-width: 1px;
  border-right-width: 0;
  border-bottom-width: 0;
  border-left-width: 1px;
}

#div_summary td, #div_summary th {
  padding: 0.1em 0.2em 0.1em 0.2em;
  border-color: #0B263B;
  border-style: solid;
  border-top-width: 0;
  border-right-width: 1px;
  border-bottom-width: 1px;
  border-left-width: 0;
}

#div_summary th {
  background-color: transparent;
  font-weight: bold;
  text-align: center;
}

#div_summary thead tr:nth-child(2) th {
  font-weight: normal;
  font-style: italic;
}

#div_summary th:first-child {
  text-align: right;
}

#div_summary tfoot th {
  text-align: right;
}

#div_summary td {
  text-align: right;
}

#div_summary tbody td:nth-child(even),
#div_summary tfoot th:nth-child(even) {
  border-right-width: 0;
}

#div_summary td:first-child {
  font-weight: bold;
}

p.report_entries {
  float: left;
  clear: left;
  font-weight: bold
}

button#delete_button {
  float: left;
  clear: left;
  margin: 1em 0 3em 0;
}

#progress_container {
  position: fixed;
  top: 50%;
  left: 50%;
  z-index: 100;
  transform: translate(-50%, -50%);
  box-shadow:0 0 0 max(100vh, 100vw) rgba(0,0,0,0.6);
  width: 20rem;
  max-width: 80%;
  text-align: center;
  padding: 1rem;
  border-radius: 0.5rem;
  background-color: white;
  display: none;
}

#progress_container progress {
  width: 100%;
  margin: 0.75rem 0;
}

/* ------------ SEARCH.PHP ----------------------*/

h3.search_results {
  clear: left;
  margin-bottom: 0;
  padding-top: 2em;
}

.search p.error {
  clear: left;
}

p#nothing_found {
  font-weight: bold;
}

div#record_numbers {
  font-weight: bold;
}

div#record_nav {
  float: left;
  margin: 0.5em 0;
}

div#record_nav form {
  float: left;
}

div#record_nav form:first-child {
  margin-right: 1em;
}


/* ------------ SITE_FAQ ------------------------*/

.help q {
  font-style: italic;
}

.help dfn {
  font-style: normal;
  font-weight: bold;
}

#site_faq_contents li a {
  text-decoration: underline;
}

div#site_faq_body {
  margin-top: 2.0em;
}

#site_faq_body h4 {
  border-top: 1px solid #C3CCD3;
  padding-top: 0.5em;
  margin-top: 0;
}

#site_faq_body div {
  padding-bottom: 0.5em;
}

#site_faq_body :target {
  background-color: #ffe6f0;
}


/* ------------ VIEW_ENTRY.PHP ------------------*/

.view_entry #entry td:first-child,
.view_entry #registration .list td:first-child {
  text-align: right;
  font-weight: bold;
  padding-right: 1.0em;
}

.view_entry #registration .list {
  margin-bottom: 1em;
}

.view_entry #entry,
.view_entry #registration {
  padding-left: 1em;
}

.view_entry #registration {
  margin-bottom: 2em;
}

.view_entry #registration p {
  margin-top: 0;
  margin-bottom: 0;
}

.view_entry #registration .standard {
  display: inline-block;
  float: none;
  margin-top: 0;
}

.view_entry #registration .standard fieldset {
  padding-top: 0;
  padding-bottom: 0;
}

.view_entry #registration .standard fieldset > div > label {
  padding-left: 0;
}

.view_entry #registrant_list {
  padding-left: 1em;
}

.view_entry #registrants thead th {
  text-align: left;
}

.view_entry #registrants th,
.view_entry #registrants td {
  vertical-align: middle;
  padding: 0.4em 0.8em;
}

.view_entry #registrants td:not(:first-child) {
  width: 33%;
}

.view_entry h4 {
  margin-top: 2em;
  margin-bottom: 1em;
  clear: left;
}

.view_entry div#view_entry_nav {
  display: table;
  margin-top: 1em;
  margin-bottom: 1em;
}

div#view_entry_nav > div {
  display: table-row;
}

div#view_entry_nav > div > div {
  display: table-cell;
  padding: 0.5em 1em;
}

#view_entry_nav input[type="submit"] {
  width: 100%;
}

.view_entry #approve_buttons form {
  float: left;
  margin-right: 2em;
}

.view_entry #approve_buttons form {
  float: left;
}

div#returl {
  margin-bottom: 1em;
}

#approve_buttons td {
  vertical-align: middle;
  padding-top: 1em;
}

#approve_buttons td#caption {
  text-align: left;
}

#approve_buttons td#note {
  padding-top: 0;
}

#approve_buttons td#note form {
  width: 100%;
}

#approve_buttons td#note textarea {
  width: 100%;
  height: 6em;
  margin-bottom: 0.5em;
}


/*-------------DataTables-------------------------*/

div.datatable_container {
  float: left;
  width: 100%;
}

.js .datatable_container {
  visibility: hidden;
}

div.ColVis_collection {
  float: left;
  width: auto;
}

div.ColVis_collection button.ColVis_Button {
  float: left;
  clear: left;
}

.dataTables_wrapper .dataTables_length {
  clear: both;
}

.dataTables_wrapper .dataTables_filter {
  clear: right;
  margin-bottom: 1em;
}

span.ColVis_radio {
  display: block;
  float: left;
  width: 30px;
}

span.ColVis_title {
  display: block;
  float: left;
  white-space: nowrap;
}

table.dataTable.display tbody tr.odd {
  background-color: #E2E4FF;
}

table.dataTable.display tbody tr.even {
  background-color: white;
}

table.dataTable.display tbody tr.odd > .sorting_1,
table.dataTable.order-column.stripe tbody tr.odd > .sorting_1 {
  background-color: #D3D6FF;
}

table.dataTable.display tbody tr.odd > .sorting_2,
table.dataTable.order-column.stripe tbody tr.odd > .sorting_2 {
  background-color: #DADCFF;
}

table.dataTable.display tbody tr.odd > .sorting_3,
table.dataTable.order-column.stripe tbody tr.odd > .sorting_3 {
  background-color: #E0E2FF;
}

table.dataTable.display tbody tr.even > .sorting_1,
table.dataTable.order-column.stripe tbody tr.even > .sorting_1  {
  background-color: #EAEBFF;
}

table.dataTable.display tbody tr.even > .sorting_2,
table.dataTable.order-column.stripe tbody tr.even > .sorting_2 {
  background-color: #F2F3FF;
}

table.dataTable.display tbody tr.even > .sorting_3,
table.dataTable.order-column.stripe tbody tr.even > .sorting_3 {
  background-color: #F9F9FF;
}

.dataTables_wrapper.no-footer .dataTables_scrollBody {
  border-bottom-width: 0;
}

div.dt-buttons {
  float: right;
  margin-bottom: 0.4em;
}

a.dt-button {
  margin-right: 0;
}


/* ------------ jQuery UI additions -------------*/

.ui-autocomplete {
  max-height: 150px;
  overflow-y: auto;
  /* prevent horizontal scrollbar */
  overflow-x: hidden;
  /* add padding to account for vertical scrollbar */
  padding-right: 20px;
}

#check_tabs {border:0}
div#check_tabs {background-image: none}
.edit_entry #ui-tab-dialog-close {position:absolute; right:0; top:23px}
.edit_entry #ui-tab-dialog-close a {float:none; padding:0}


.ui-dialog {
  z-index: 800;
}


.flatpickr-day.selected,
.flatpickr-day.startRange,
.flatpickr-day.endRange,
.flatpickr-day.selected.inRange,
.flatpickr-day.startRange.inRange,
.flatpickr-day.endRange.inRange,
.flatpickr-day.selected:focus,
.flatpickr-day.startRange:focus,
.flatpickr-day.endRange:focus,
.flatpickr-day.selected:hover,
.flatpickr-day.startRange:hover,
.flatpickr-day.endRange:hover,
.flatpickr-day.selected.prevMonthDay,
.flatpickr-day.startRange.prevMonthDay,
.flatpickr-day.endRange.prevMonthDay,
.flatpickr-day.selected.nextMonthDay,
.flatpickr-day.startRange.nextMonthDay,
.flatpickr-day.endRange.nextMonthDay {
  background: #0b2147;
  border-color: #0b2147;
}

.flatpickr-day.selected.startRange + .endRange,
.flatpickr-day.startRange.startRange + .endRange,
.flatpickr-day.endRange.startRange + .endRange {
  -webkit-box-shadow: -10px 0 0 #0b2147;
  box-shadow: -10px 0 0 #0b2147;
}

.flatpickr-day.week.selected {
  -webkit-box-shadow:-5px 0 0 #0b2147, 5px 0 0 #0b2147;
  box-shadow:-5px 0 0 #0b2147, 5px 0 0 #0b2147;
}

.flatpickr-day.mrbs-hidden {
  color: rgba(57,57,57,0.1);
}

.flatpickr-calendar, .flatpickr-days {
    width: auto !important;
}

h2.date.loading,
h2.date.loading::after {
  animation-name: pulsate;
  animation-duration: 2s;
  animation-timing-function: ease-in-out;
  animation-iteration-count: infinite;
}

.ajax-loading input.select2-search__field {
  background-image: url(../images/ajax-loader.gif);
  background-position: center right 5px;
  background-repeat: no-repeat;
}

div.eye {
  height: 1em;
  display: inline-block !important;
}

.eye svg {
  position: relative;
  height: 1em;
  margin-left: -3em;
  cursor: pointer;
}

.eye.off svg {
  display: none;
}

[data-kiosk] .minicalendars,
[data-kiosk] nav.arrow,
[data-kiosk] nav.view,
[data-kiosk] .banner nav:not(.qr) {
  display: none;
}

nav.qr {
  padding: 0.5rem 0;
  margin-left: auto;
}

.qr-svg {
  width: 5rem !important;
  position: relative;
  top: 50%;
  -ms-transform: translateY(-50%);
  transform: translateY(-50%);
}



