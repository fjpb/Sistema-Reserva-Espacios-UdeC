<?php
declare(strict_types=1);
namespace MRBS;

/**
 * Tema Institucional UdeC - Universidad de Concepción
 * Campus Los Ángeles - Unidad TI / DTI
 * Basado en: Demo nuevo html/sistema_de_reservas_udec_tema_institucional_mrbs.html
 */

// ***** COLORES GENERALES ************************
$body_background_color          = "#F8FAFC";    // Fondo slate-50 limpio
$standard_font_color            = "#1E293B";    // slate-800
$header_font_color              = "#ffffff";    // Blanco puro para cabeceras
$highlight_font_color           = "#E89D1E";    // Dorado UdeC
$color_key_font_color           = $standard_font_color;

// Barra superior institucional (Navy UdeC con borde Dorado)
$banner_back_color              = "#002B49";    // Navy UdeC
$banner_border_color            = "#E89D1E";    // Borde inferior Dorado
$banner_font_color              = "#ffffff";
$banner_nav_hover_color         = "#003865";    // Azul UdeC Hover

// Mini calendarios y datepicker
$minical_view_color             = "#003865";
$minical_today_color            = "#E89D1E";
$header_back_color              = "#002B49";
$flatpickr_highlight_color      = "#003865";

// Tablas de Administración
$admin_table_header_back_color  = "#002B49";
$admin_table_header_sep_color   = "#E2E8F0";
$admin_table_header_font_color  = "#ffffff";
$admin_table_border_color       = "#CBD5E1";

// Tabla Principal de Horarios (Día, Semana, Mes)
$main_table_border_color        = "#E2E8F0";    // Bordes sutiles y elegantes
$main_table_header_border_color = "#CBD5E1";
$main_table_body_h_border_color = "#E2E8F0";
$main_table_body_v_border_color = "#E2E8F0";
$main_table_month_color         = "#ffffff";
$main_table_month_weekend_color = "#F1F5F9";
$main_table_month_holiday_color = "#E2E8F0";
$main_table_month_weekend_holiday_color = "#CBD5E1";
$main_table_month_invalid_color = "#E2E8F0";
$main_table_slot_invalid_color  = "#F1F5F9";
$main_table_slot_private_type_color = "#E2E8F0";
$main_table_labels_back_color   = "#002B49";
$timeline_color                 = "#E89D1E";

// Impresión
$main_table_border_color_print        = "#CBD5E1";
$main_table_header_border_color_print = "#CBD5E1";
$main_table_body_h_border_color_print = "#E2E8F0";
$main_table_body_v_border_color_print = "#E2E8F0";
$header_font_color_print        = "#002B49";
$anchor_link_color_header_print = "#002B49";

// Reportes y Búsqueda
$report_table_border_color      = "#CBD5E1";
$report_h2_border_color         = "#002B49";
$report_h3_border_color         = "#E89D1E";
$search_table_border_color      = "#CBD5E1";
$site_faq_entry_border_color    = "#E2E8F0";

// Enlaces y Anclas
$anchor_link_color              = "#003865";
$anchor_visited_color           = "#003865";
$anchor_hover_color             = "#E89D1E";

$anchor_link_color_banner       = "#ffffff";
$anchor_visited_color_banner    = "#ffffff";
$anchor_hover_color_banner      = "#E89D1E";

$anchor_link_color_header       = "#ffffff";
$anchor_visited_color_header    = "#ffffff";
$anchor_hover_color_header      = "#E89D1E";

// Filas y Celdas
$column_hidden_color            = "#E2E8F0";
$calendar_hidden_color          = "#E2E8F0";
$row_highlight_color            = "#FEF3C7"; // Resaltado dorado tenue
$row_even_color                 = "#ffffff";
$row_odd_color                  = "#F8FAFC";
$row_even_color_weekend         = "#F1F5F9";
$row_odd_color_weekend          = "#E2E8F0";
$row_even_color_holiday         = "#E2E8F0";
$row_odd_color_holiday          = "#CBD5E1";
$row_even_color_weekend_holiday = "#E2E8F0";
$row_odd_color_weekend_holiday  = "#CBD5E1";

$zebra_even_color = "#ffffff";
$zebra_odd_color  = "#F1F5F9";

$help_highlight_color           = "#FEF3C7";

// Botones Institucionales
$button_color_stops = array('#003865', '#002B49');
$button_inset_color = '#E89D1E';

// Colores Institucionales de Carreras y Tipos de Reserva (Carga Dinámica desde Panel o Fallback)
$types_config_file = __DIR__ . '/types_config.json';
if (is_file($types_config_file)) {
  $loaded_types = json_decode(file_get_contents($types_config_file), true);
  if (is_array($loaded_types) && !empty($loaded_types)) {
    $color_types = array();
    foreach ($loaded_types as $t) {
      $color_types[$t['code']] = $t['color'];
    }
  }
}

if (empty($color_types)) {
  $color_types = array(
    'A' => "#92D050", // General
    'B' => "#9999FF", // Auditoría
    'C' => "#F1A983", // Ing. Comercial
    'D' => "#94DCF8", // Ing. Geomática
    'E' => "#CBD5E1", // Externa
    'F' => "#A9D08E", // Ing. Biotecnología
    'G' => "#FFFF99", // PCNB
    'H' => "#F2CEEF", // Ped. Matemáticas
    'I' => "#6EE7B7", // Interna
    'J' => "#6FF99D", // Ped. Inglés
    'K' => "#FFC000", // Ped. Español
    'L' => "#6DE3FB", // EGB
    'M' => "#F64C99", // Ed. Diferencial
    'N' => "#FFFF00", // Enfermería
    'O' => "#AE57D5", // CADE
    'P' => "#CC9900", // Escuela Educación
    'Q' => "#0099FF", // Escuela Ciencias
    'R' => "#CCFF99", // Dirección General
    'S' => "#20D2CA", // Vinculación
    'T' => "#AFEB67", // Subdirección Académica
    'U' => "#FB9FD4", // Extensión AyC
    'V' => "#04A4A0", // Deportes
    'X' => "#62B319", // Administración
    'Y' => "#E7E200"  // Investigación e Innovación
  );
}

$outstanding_color         = "#FEF08A";
$pending_header_back_color = "#002B49";
$series_entry_back_color   = "#FEF3C7";
$pending_control_color     = "#E89D1E";
$attention_color           = "#E89D1E";

// DIMENSIONES
$banner_border_width          = '0';
$banner_border_cell_width     = '1';
$main_table_border_width      = '1';
$main_table_cell_border_width = '1';
$main_cell_height             = '2.4em';

// TIPOGRAFÍA MODERNA INTER
$standard_font_family  = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif";
