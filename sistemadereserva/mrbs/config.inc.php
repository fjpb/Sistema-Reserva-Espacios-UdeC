<?php // -*-mode: PHP; coding:utf-8;-*-
declare(strict_types=1);
namespace MRBS;

use IntlDateFormatter;

require_once 'lib/autoload.inc';

/**************************************************************************
 *   MRBS Configuration File
 *   Configure this file for your site.
 *   You shouldn't have to modify anything outside this file.
 *
 *   This file has already been populated with the minimum set of configuration
 *   variables that you will need to change to get your system up and running.
 *   If you want to change any of the other settings in systemdefaults.inc.php
 *   or areadefaults.inc.php, then copy the relevant lines into this file
 *   and edit them here.   This file will override the default settings and
 *   when you upgrade to a new version of MRBS the config file is preserved.
 *
 *   NOTE: if you include or require other files from this file, for example
 *   to store your database details in a separate location, then you should
 *   use an absolute and not a relative pathname.
 **************************************************************************/

/**********
 * Timezone
 **********/

// The timezone your meeting rooms run in. It is especially important
// to set this if you're using PHP 5 on Linux. In this configuration
// if you don't, meetings in a different DST than you are currently
// in are offset by the DST offset incorrectly.
//
// Note that timezones can be set on a per-area basis, so strictly speaking this
// setting should be in areadefaults.inc.php, but as it is so important to set
// the right timezone it is included here.
//
// When upgrading an existing installation, this should be set to the
// timezone the web server runs in.  See the INSTALL document for more information.
//
// A list of valid timezones can be found at http://php.net/manual/timezones.php
// The following line must be uncommented by removing the '//' at the beginning
$timezone = "America/Santiago";
$override_locale = "es-CL";
$default_language_tokens = "es";


/*******************
 * Database settings
 ******************/
// Which database system: "pgsql"=PostgreSQL, "mysql"=MySQL
$dbsys = getenv('DB_SYS') ?: "mysql";
// Hostname of database server. For pgsql, can use "" instead of localhost
// to use Unix Domain Sockets instead of TCP/IP. For mysql "localhost"
// tells the system to use Unix Domain Sockets, and $db_port will be ignored;
// if you want to force TCP connection you can use "127.0.0.1".
$db_host = getenv('DB_HOST') ?: "localhost";
// If you need to use a non standard port for the database connection you
// can uncomment the following line and specify the port number
if (getenv('DB_PORT')) {
  $db_port = (int)getenv('DB_PORT');
}
// Database name:
$db_database = getenv('DB_DATABASE') ?: "unidadti";
// Schema name.  This only applies to PostgreSQL and is only necessary if you have more
// than one schema in your database and also you are using the same MRBS table names in
// multiple schemas.
//$db_schema = "public";
// Database login user name:
$db_login = getenv('DB_LOGIN') ?: "unidadti";
// Database login password:
$db_password = getenv('DB_PASSWORD') ?: '6gsi26SzTZzmwRQW';
// Prefix for table names.  This will allow multiple installations where only
// one database is available
$db_tbl_prefix = getenv('DB_TBL_PREFIX') ?: "mrbs_";
// Set $db_persist to TRUE to use PHP persistent (pooled) database connections.  Note
// that persistent connections are not recommended unless your system suffers significant
// performance problems without them.   They can cause problems with transactions and
// locks (see http://php.net/manual/en/features.persistent-connections.php) and although
// MRBS tries to avoid those problems, it is generally better not to use persistent
// connections if you can.
$db_persist = false;


/* Add lines from systemdefaults.inc.php and areadefaults.inc.php below here
   to change the default configuration. Do _NOT_ modify systemdefaults.inc.php
   or areadefaults.inc.php.  */

/*******************
 * Themes & Estilo Institucional UdeC
 *******************/
$theme = "udec";
$custom_css_url = "Themes/udec/custom.css";
$custom_js_url = "Themes/udec/custom.js";

// Ocultar mini-calendarios laterales por defecto para pantalla completa
$display_mincals = false;

$mrbs_company = "DTI UdeC Campus Los Ángeles";   // This line must always be uncommented ($mrbs_company is used in various places)

// Activar modo de periodos personalizados
$enable_periods = TRUE;
$default_view = "week";
$times_along_top = FALSE;
$default_duration = 2700;  // duración sugerida al crear reserva

// Definición de bloques personalizados
$periods = array(
  "08:15 - 09:00",
  "09:00 - 09:45",
  "09:50 - 10:35",
  "10:35 - 11:20",
  "11:25 - 12:10",
  "12:10 - 12:55",
  "13:00 - 13:45",
  "13:45 - 14:30",
  "14:35 - 15:20",
  "15:20 - 16:05",
  "16:10 - 16:55",
  "16:55 - 17:40",
  "17:45 - 18:30"
);

// Día empieza en lunes
$weekstarts = 1;

// Ocultar domingos
$hidden_days = array(0);

// $hidden_days = array(0,6); (Opcional) Ocultar también sábados:

// --- Tipos de Horario y Vocabulario (Carga Dinámica desde Panel Web o Fallback) ---
$types_config_file = __DIR__ . '/Themes/udec/types_config.json';
if (is_file($types_config_file)) {
  $loaded_types = json_decode(file_get_contents($types_config_file), true);
  if (is_array($loaded_types) && !empty($loaded_types)) {
    $booking_types = array();
    foreach ($loaded_types as $t) {
      if (!empty($t['enabled'])) {
        $c = $t['code'];
        $booking_types[] = $c;
        $vocab_override["es"]["type.$c"] = $t['name'];
        $vocab_override["en"]["type.$c"] = $t['name'];
      }
    }
  }
}

if (empty($booking_types)) {
  // Fallback por defecto si no existe el archivo JSON
  $booking_types = array("E", "I", "A", "B", "C", "D", "F", "G", "H", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "X", "Y");
  $vocab_override["es"]["type.A"] = "General";
  $vocab_override["es"]["type.B"] = "Auditoría";
  $vocab_override["es"]["type.C"] = "Ingeniería Comercial";
  $vocab_override["es"]["type.D"] = "Ingeniería Geomática";
  $vocab_override["es"]["type.F"] = "Ingeniería en Biotecnología Vegetal";
  $vocab_override["es"]["type.G"] = "Pedagogía en Ciencias Naturales y Biología (PCNB)";
  $vocab_override["es"]["type.H"] = "Pedagogía en Matemáticas";
  $vocab_override["es"]["type.J"] = "Pedagogía en Inglés";
  $vocab_override["es"]["type.K"] = "Pedagogía en Español";
  $vocab_override["es"]["type.L"] = "Educación General Básica";
  $vocab_override["es"]["type.M"] = "Educación Diferencial";
  $vocab_override["es"]["type.N"] = "Enfermería";
  $vocab_override["es"]["type.O"] = "CADE";
  $vocab_override["es"]["type.P"] = "Escuela Educación";
  $vocab_override["es"]["type.Q"] = "Escuela Ciencias";
  $vocab_override["es"]["type.R"] = "Direccion General";
  $vocab_override["es"]["type.S"] = "Unidad Vinculacion con el Medio";
  $vocab_override["es"]["type.T"] = "Subdirección Académica";
  $vocab_override["es"]["type.U"] = "Oficina Extensión AyC";
  $vocab_override["es"]["type.V"] = "Unidad de Deportes";
  $vocab_override["es"]["type.X"] = "Unidad Administración";
  $vocab_override["es"]["type.Y"] = "Unidad Investigación e Innovación";
}

// Vocabulario para los nuevos módulos de administración visual
$vocab_override["es"]["admin_types"] = "Tipos y Colores";
$vocab_override["en"]["admin_types"] = "Types & Colors";
$vocab_override["es"]["admin_pending"] = "Pendientes";
$vocab_override["en"]["admin_pending"] = "Pending";

// --- Campo Personalizado: Solicitante ---
$edit_entry_field_order = array('name', 'solicitante', 'description', 'start_time', 'end_time', 'room_id', 'type');
$vocab_override["es"]["entry.solicitante"] = "Solicitante";
$vocab_override["en"]["entry.solicitante"] = "Requester";
