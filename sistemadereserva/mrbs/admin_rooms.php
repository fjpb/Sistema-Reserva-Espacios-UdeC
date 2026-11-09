<?php
declare(strict_types=1);
namespace MRBS;

use MRBS\Form\Form;

require "defaultincludes.inc";
require_once "mrbs_sql.inc";
require_once "functions_table.inc";

// Guardar preferencia de versión moderna si fue solicitada por parámetro
if (isset($_GET['set_view']) && $_GET['set_view'] === 'modern')
{
  setcookie('mrbs_rooms_view', 'modern', time() + 31536000, '/');
  $_COOKIE['mrbs_rooms_view'] = 'modern';
}

// Validar autenticación y permisos de Administrador
Form::checkToken(true);
checkAuthorised(this_page());

if (!is_admin() && !is_book_admin())
{
  showAccessDenied();
  exit;
}

$msg_success = '';
$msg_error   = '';

// =========================================================================
// FUNCIÓN AUXILIAR: PARSEO DE MANTENCIÓN / FUERA DE SERVICIO
// =========================================================================
function parse_room_maintenance(?string $custom_html): array
{
  $def = array(
    'enabled'     => false,
    'mode'        => 'mantencion', // 'mantencion' | 'fuera_de_servicio'
    'from'        => '',
    'to'          => '',
    'reason'      => '',
    'from_ts'     => 0,
    'to_ts'       => 0,
    'is_current'  => false,
    'is_future'   => false,
    'is_past'     => false
  );

  if (empty($custom_html))
  {
    return $def;
  }

  $json = json_decode($custom_html, true);
  if (!is_array($json) || empty($json['udec_maintenance']))
  {
    return $def;
  }

  $m = $json['udec_maintenance'];
  $enabled = !empty($m['enabled']);
  $mode = (($m['mode'] ?? 'mantencion') === 'fuera_de_servicio') ? 'fuera_de_servicio' : 'mantencion';
  $from = (string)($m['from'] ?? '');
  $to   = (string)($m['to'] ?? '');
  $reason = (string)($m['reason'] ?? '');

  $from_ts = !empty($from) ? (int)strtotime($from) : 0;
  $to_ts   = !empty($to)   ? (int)strtotime($to)   : 0;
  $now = time();

  $is_current = false;
  $is_future  = false;
  $is_past    = false;

  if ($enabled)
  {
    if ($from_ts === 0 && $to_ts === 0)
    {
      $is_current = true;
    }
    elseif ($from_ts > 0 && $now < $from_ts)
    {
      $is_future = true;
    }
    elseif ($to_ts > 0 && $now > $to_ts)
    {
      $is_past = true;
    }
    else
    {
      $is_current = true;
    }
  }

  return array(
    'enabled'     => $enabled,
    'mode'        => $mode,
    'from'        => $from,
    'to'          => $to,
    'reason'      => $reason,
    'from_ts'     => $from_ts,
    'to_ts'       => $to_ts,
    'entry_id'    => (int)($m['entry_id'] ?? 0),
    'is_current'  => $is_current,
    'is_future'   => $is_future,
    'is_past'     => $is_past
  );
}

function get_room_custom_html(int $room_id): ?string
{
  if ($room_id <= 0)
  {
    return null;
  }
  $res = db()->query("SELECT custom_html FROM " . _tbl('room') . " WHERE id = ? LIMIT 1", array($room_id));
  if ($res->count() > 0)
  {
    $row = $res->next_row_keyed();
    return $row['custom_html'] ?? null;
  }
  return null;
}

// =========================================================================
// PROCESAMIENTO DE ACCIONES POST (Guardar, Cambiar Estado, Eliminar)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
  $action = $_POST['action'] ?? '';

  // 1. Guardar Recinto (Crear o Modificar)
  if ($action === 'save_room')
  {
    $room_id          = (int)($_POST['room_id'] ?? 0);
    $area_id          = (int)($_POST['area_id'] ?? 0);
    $room_name        = trim((string)($_POST['room_name'] ?? ''));
    $capacity         = max(0, (int)($_POST['capacity'] ?? 0));
    $room_admin_email = trim((string)($_POST['room_admin_email'] ?? ''));
    $room_type        = trim((string)($_POST['room_type'] ?? 'Normal'));
    $notes            = trim((string)($_POST['notes'] ?? ''));

    // Parámetros de Mantención / Fuera de Servicio
    $maint_enabled = (!empty($_POST['maint_enabled']) && $_POST['maint_enabled'] !== '0') ? 1 : 0;
    $maint_mode    = trim((string)($_POST['maint_mode'] ?? 'mantencion'));
    if ($maint_mode !== 'fuera_de_servicio') { $maint_mode = 'mantencion'; }
    $maint_from    = trim((string)($_POST['maint_from'] ?? ''));
    $maint_to      = trim((string)($_POST['maint_to'] ?? ''));
    $maint_reason  = trim((string)($_POST['maint_reason'] ?? ''));

    // IMPORTANTE: El espacio NUNCA se oculta de la grilla (disabled permanece en 0)
    $disabled = 0;
    $custom_html = null;

    if ($room_name === '')
    {
      $msg_error = 'El nombre de la sala no puede estar vacío.';
    }
    elseif ($area_id <= 0)
    {
      $msg_error = 'Debe seleccionar un edificio o área válida.';
    }
    else
    {
      try
      {
        // Ensamblar equipamiento seleccionado
        $equip_items = array();
        if (!empty($_POST['equip_proyector'])) { $equip_items[] = 'Proyector'; }
        if (!empty($_POST['equip_tactil']))    { $equip_items[] = 'Pantalla Táctil'; }
        if (!empty($_POST['equip_pc']))        { $equip_items[] = 'PC'; }
        if (!empty($_POST['equip_audio']))     { $equip_items[] = 'Audio AUX/BT'; }
        if (!empty($_POST['equip_ac']))        { $equip_items[] = 'A/C'; }
        if (!empty($_POST['equip_tv']))        { $equip_items[] = 'TV'; }

        // Agregar equipamiento personalizado si fue ingresado dinámicamente
        $custom_equip_raw = trim((string)($_POST['custom_equip'] ?? ''));
        if ($custom_equip_raw !== '')
        {
          $c_items = array_map('trim', explode(',', $custom_equip_raw));
          foreach ($c_items as $ci)
          {
            if ($ci !== '' && !in_array($ci, $equip_items, true))
            {
              $equip_items[] = $ci;
            }
          }
        }

        // Componer descripción institucional: Tipo: [Tipo] | [Equipamiento] | [Notas]
        $desc_parts = array();
        if ($room_type !== '')
        {
          $desc_parts[] = 'Tipo: ' . $room_type;
        }
        if (!empty($equip_items))
        {
          $desc_parts[] = implode(', ', $equip_items);
        }
        if ($notes !== '')
        {
          $desc_parts[] = $notes;
        }
        $description = implode(' | ', $desc_parts);

        // Guardar o insertar sala preliminarmente si es nueva para obtener ID
        if ($room_id <= 0)
        {
          $sql_ins = "INSERT INTO " . _tbl('room') . "
                      (room_name, sort_key, area_id, capacity, description, room_admin_email, disabled, custom_html)
                      VALUES (?, ?, ?, ?, ?, ?, 0, NULL)";
          db()->command($sql_ins, array($room_name, $room_name, $area_id, $capacity, $description, $room_admin_email));
          $room_id = (int)db()->insert_id(_tbl('room'), 'id');
        }

        // Procesar bloqueo en calendario para Mantención / Fuera de Servicio
        if ($maint_enabled === 1)
        {
          $from_dt = !empty($maint_from) ? (int)strtotime($maint_from) : time();
          $to_dt   = !empty($maint_to)   ? (int)strtotime($maint_to)   : ($from_dt + (86400 * 2));
          if ($to_dt <= $from_dt)
          {
            $to_dt = $from_dt + 3600;
          }

          // Consultar si el área utiliza periodos académicos (UdeC Campus Los Ángeles)
          $area_settings = get_area_settings($area_id);
          $is_periods = !empty($area_settings['enable_periods']);

          if ($is_periods)
          {
            $from_parts = getdate($from_dt);
            $to_parts   = getdate($to_dt);
            $period_names = get_period_names();
            $period_count = isset($period_names[$area_id]) ? count($period_names[$area_id]) : 16;
            if ($period_count <= 0) { $period_count = 16; }

            // En modo periodos, los slots de cada día van desde 12:00:00 (periodo 0) hasta 12:00:00 + (n_periodos * 60)
            $from_ts = mktime(12, 0, 0, $from_parts['mon'], $from_parts['mday'], $from_parts['year']);
            $to_ts   = mktime(12, $period_count, 0, $to_parts['mon'], $to_parts['mday'], $to_parts['year']);
          }
          else
          {
            $from_ts = $from_dt;
            $to_ts   = $to_dt;
          }

          $maint_prefix = ($maint_mode === 'fuera_de_servicio') ? '⛔ FUERA DE SERVICIO' : '🔧 EN MANTENCIÓN';
          $entry_name = !empty($maint_reason) ? ($maint_prefix . ': ' . $maint_reason) : $maint_prefix;
          $entry_desc = !empty($maint_reason) ? $maint_reason : ($maint_mode === 'fuera_de_servicio' ? 'Recinto fuera de servicio temporal' : 'Mantención técnica programada');
          $solicitante = 'Unidad de Mantención e Infraestructura';

          // Verificar si ya existía una entrada asociada
          $prev_custom = get_room_custom_html($room_id);
          $prev_maint = parse_room_maintenance($prev_custom);
          $existing_entry_id = $prev_maint['entry_id'] ?? 0;

          $entry_saved = false;
          if ($existing_entry_id > 0)
          {
            $cnt = (int)db()->query1("SELECT COUNT(*) FROM " . _tbl('entry') . " WHERE id = ?", array($existing_entry_id));
            if ($cnt > 0)
            {
              $sql_entry = "UPDATE " . _tbl('entry') . "
                               SET start_time = ?,
                                   end_time = ?,
                                   name = ?,
                                   description = ?,
                                   solicitante = ?,
                                   type = 'I',
                                   status = 0
                             WHERE id = ?";
              db()->command($sql_entry, array($from_ts, $to_ts, $entry_name, $entry_desc, $solicitante, $existing_entry_id));
              $entry_id = $existing_entry_id;
              $entry_saved = true;
            }
          }

          if (!$entry_saved)
          {
            $current_user = session()->getCurrentUser();
            $created_by = (isset($current_user) && !empty($current_user->username)) ? $current_user->username : 'administrador';
            $sql_entry = "INSERT INTO " . _tbl('entry') . "
                          (start_time, end_time, entry_type, repeat_id, room_id, timestamp, create_by, name, type, description, solicitante, status)
                          VALUES (?, ?, 0, NULL, ?, NOW(), ?, ?, 'I', ?, ?, 0)";
            db()->command($sql_entry, array($from_ts, $to_ts, $room_id, $created_by, $entry_name, $entry_desc, $solicitante));
            $entry_id = (int)db()->insert_id(_tbl('entry'), 'id');
          }

          $maint_payload = array(
            'udec_maintenance' => array(
              'enabled'    => true,
              'mode'       => $maint_mode,
              'from'       => $maint_from,
              'to'         => $maint_to,
              'start_ts'   => $from_dt,
              'end_ts'     => $to_dt,
              'reason'     => $maint_reason,
              'entry_id'   => $entry_id,
              'updated_at' => date('Y-m-d H:i:s')
            )
          );
          $custom_html = json_encode($maint_payload, JSON_UNESCAPED_UNICODE);
        }
        else
        {
          // Si se desactiva mantención, limpiar las reservas de bloqueo en el calendario
          $prev_custom = get_room_custom_html($room_id);
          $prev_maint = parse_room_maintenance($prev_custom);
          if (!empty($prev_maint['entry_id']))
          {
            db()->command("DELETE FROM " . _tbl('entry') . " WHERE id = ?", array($prev_maint['entry_id']));
          }
          db()->command("DELETE FROM " . _tbl('entry') . " WHERE room_id = ? AND (name LIKE '%MANTENCIÓN%' OR name LIKE '%MANTENCION%' OR name LIKE '%FUERA DE SERVICIO%')", array($room_id));
          $custom_html = null;
        }

        // Actualizar datos del recinto (garantizando disabled = 0 para que no desaparezca)
        if ($custom_html !== null)
        {
          $sql = "UPDATE " . _tbl('room') . "
                     SET room_name = ?,
                         sort_key = ?,
                         area_id = ?,
                         capacity = ?,
                         description = ?,
                         room_admin_email = ?,
                         disabled = 0,
                         custom_html = ?
                   WHERE id = ?";
          $sql_params = array($room_name, $room_name, $area_id, $capacity, $description, $room_admin_email, $custom_html, $room_id);
        }
        else
        {
          $sql = "UPDATE " . _tbl('room') . "
                     SET room_name = ?,
                         sort_key = ?,
                         area_id = ?,
                         capacity = ?,
                         description = ?,
                         room_admin_email = ?,
                         disabled = 0,
                         custom_html = NULL
                   WHERE id = ?";
          $sql_params = array($room_name, $room_name, $area_id, $capacity, $description, $room_admin_email, $room_id);
        }
        db()->command($sql, $sql_params);

        $msg_success = ($maint_enabled === 1)
          ? "Recinto \"$room_name\" guardado. Estado de mantención activado y visible en el calendario con su motivo."
          : "Recinto \"$room_name\" guardado exitosamente. La sala está 100% operativa.";
      }
      catch (\Throwable $e)
      {
        $msg_error = "Error al guardar el recinto: " . htmlspecialchars($e->getMessage());
      }
    }
  }

  // 2. Reactivar Sala / Finalizar Mantención (1 Clic)
  elseif ($action === 'clear_maintenance')
  {
    $room_id = (int)($_POST['room_id'] ?? 0);
    if ($room_id > 0)
    {
      try
      {
        $prev_custom = get_room_custom_html($room_id);
        $prev_maint = parse_room_maintenance($prev_custom);
        if (!empty($prev_maint['entry_id']))
        {
          db()->command("DELETE FROM " . _tbl('entry') . " WHERE id = ?", array($prev_maint['entry_id']));
        }
        db()->command("DELETE FROM " . _tbl('entry') . " WHERE room_id = ? AND (name LIKE '%MANTENCIÓN%' OR name LIKE '%MANTENCION%' OR name LIKE '%FUERA DE SERVICIO%')", array($room_id));
        db()->command("UPDATE " . _tbl('room') . " SET disabled = 0, custom_html = NULL WHERE id = ?", array($room_id));
        $msg_success = "La sala fue reactivada exitosamente y quedó 100% operativa en el calendario.";
      }
      catch (\Throwable $e)
      {
        $msg_error = "Error al reactivar la sala: " . htmlspecialchars($e->getMessage());
      }
    }
  }

  // 3. Conmutar Estado Rápido
  elseif ($action === 'toggle_status')
  {
    $room_id = (int)($_POST['room_id'] ?? 0);
    $curr_disabled = (int)($_POST['current_disabled'] ?? 0);
    $new_disabled = ($curr_disabled === 1) ? 0 : 1;

    if ($room_id > 0)
    {
      try
      {
        if ($new_disabled === 0)
        {
          $prev_custom = get_room_custom_html($room_id);
          $prev_maint = parse_room_maintenance($prev_custom);
          if (!empty($prev_maint['entry_id']))
          {
            db()->command("DELETE FROM " . _tbl('entry') . " WHERE id = ?", array($prev_maint['entry_id']));
          }
          db()->command("DELETE FROM " . _tbl('entry') . " WHERE room_id = ? AND (name LIKE '%MANTENCIÓN%' OR name LIKE '%FUERA DE SERVICIO%')", array($room_id));
          db()->command("UPDATE " . _tbl('room') . " SET disabled = 0, custom_html = NULL WHERE id = ?", array($room_id));
          $msg_success = "La sala fue habilitada y está 100% operativa para reservas.";
        }
        else
        {
          $now_ts = time();
          $end_ts = $now_ts + (86400 * 2);
          $current_user = session()->getCurrentUser();
          $created_by = (isset($current_user) && !empty($current_user->username)) ? $current_user->username : 'administrador';

          $sql_entry = "INSERT INTO " . _tbl('entry') . "
                        (start_time, end_time, entry_type, repeat_id, room_id, timestamp, create_by, name, type, description, solicitante, status)
                        VALUES (?, ?, 0, NULL, ?, NOW(), ?, '🔧 EN MANTENCIÓN', 'I', 'Mantención técnica', 'Unidad de Mantención e Infraestructura', 0)";
          db()->command($sql_entry, array($now_ts, $end_ts, $room_id, $created_by));
          $entry_id = (int)db()->insert_id(_tbl('entry'), 'id');

          $maint_payload = array(
            'udec_maintenance' => array(
              'enabled'    => true,
              'mode'       => 'mantencion',
              'from'       => date('Y-m-d\TH:i', $now_ts),
              'to'         => date('Y-m-d\TH:i', $end_ts),
              'start_ts'   => $now_ts,
              'end_ts'     => $end_ts,
              'reason'     => 'Mantención técnica',
              'entry_id'   => $entry_id,
              'updated_at' => date('Y-m-d H:i:s')
            )
          );
          $custom_html = json_encode($maint_payload, JSON_UNESCAPED_UNICODE);
          db()->command("UPDATE " . _tbl('room') . " SET disabled = 0, custom_html = ? WHERE id = ?", array($custom_html, $room_id));
          $msg_success = "La sala fue puesta en mantención y bloqueada visualmente en el calendario.";
        }
      }
      catch (\Throwable $e)
      {
        $msg_error = "Error al actualizar estado de la sala: " . htmlspecialchars($e->getMessage());
      }
    }
  }

  // 4. Eliminar Sala (Con resguardo estricto de historial)
  elseif ($action === 'delete_room')
  {
    $room_id = (int)($_POST['room_id'] ?? 0);
    if ($room_id > 0)
    {
      $bookings_count = (int)db()->query1("SELECT COUNT(*) FROM " . _tbl('entry') . " WHERE room_id = ?", array($room_id));
      if ($bookings_count > 0)
      {
        $msg_error = "No es posible eliminar esta sala porque tiene $bookings_count reservas asociadas en el historial. Para sacarla de servicio, márcala en modo Mantención o Fuera de Servicio.";
      }
      else
      {
        try
        {
          db()->command("DELETE FROM " . _tbl('room') . " WHERE id = ?", array($room_id));
          $msg_success = "El recinto ha sido eliminado exitosamente.";
        }
        catch (\Throwable $e)
        {
          $msg_error = "Error al eliminar la sala: " . htmlspecialchars($e->getMessage());
        }
      }
    }
  }
}

// =========================================================================
// OBTENCIÓN DE DATOS DEL CAMPUS
// =========================================================================
// 1. Áreas / Edificios
$areas_res = db()->query("SELECT id, area_name, disabled FROM " . _tbl('area') . " ORDER BY sort_key, area_name");
$areas = array();
$areas_by_id = array();
while ($row = $areas_res->next_row_keyed())
{
  $areas[] = $row;
  $areas_by_id[(int)$row['id']] = $row['area_name'];
}

// 2. Salas
$sql_rooms = "SELECT R.id, R.area_id, R.room_name, R.sort_key, R.description, R.capacity, R.room_admin_email, R.disabled, R.custom_html,
                     A.area_name, A.disabled AS area_disabled
                FROM " . _tbl('room') . " R
          INNER JOIN " . _tbl('area') . " A ON R.area_id = A.id
               ORDER BY A.sort_key, A.area_name, R.sort_key, R.room_name";
$rooms_res = db()->query($sql_rooms);
$rooms = array();
while ($row = $rooms_res->next_row_keyed())
{
  $rooms[] = $row;
}

// 3. Ocupación de Hoy y En Vivo
$today_start = mktime(0, 0, 0);
$today_end   = mktime(23, 59, 59);
$now_ts      = time();

$today_counts = array();
$tc_res = db()->query("SELECT room_id, COUNT(*) as c FROM " . _tbl('entry') . " WHERE start_time >= ? AND start_time <= ? GROUP BY room_id", array($today_start, $today_end));
while ($tc = $tc_res->next_row_keyed())
{
  $today_counts[(int)$tc['room_id']] = (int)$tc['c'];
}

$busy_now = array();
$bn_res = db()->query("SELECT DISTINCT room_id FROM " . _tbl('entry') . " WHERE start_time <= ? AND end_time > ?", array($now_ts, $now_ts));
while ($bn = $bn_res->next_row_keyed())
{
  $busy_now[(int)$bn['room_id']] = true;
}

// 4. Métricas Ejecutivas
$total_rooms          = count($rooms);
$total_capacity       = 0;
$count_maintenance    = 0;
$count_out_of_service = 0;
$count_busy_now       = count($busy_now);

foreach ($rooms as $r)
{
  $maint = parse_room_maintenance($r['custom_html'] ?? null);
  $is_dis = ($r['disabled'] == 1 || $r['area_disabled'] == 1 || $maint['enabled']);

  if ($maint['enabled'])
  {
    if ($maint['mode'] === 'fuera_de_servicio')
    {
      $count_out_of_service++;
    }
    else
    {
      $count_maintenance++;
    }
  }
  elseif ($is_dis)
  {
    $count_maintenance++;
  }
  else
  {
    $total_capacity += (int)$r['capacity'];
  }
}

// Función auxiliar para parsear descripción
function parse_room_description(?string $desc): array
{
  $res = array(
    'type' => 'Normal',
    'equip' => array(),
    'notes' => '',
    'raw_equip' => array(),
    'custom_equip' => array()
  );

  if (empty($desc))
  {
    return $res;
  }

  $parts = array_map('trim', explode('|', $desc));
  foreach ($parts as $idx => $p)
  {
    if (stripos($p, 'tipo:') === 0)
    {
      $res['type'] = trim(substr($p, 5));
    }
    elseif (stripos($p, 'notas:') === 0 || stripos($p, 'obs:') === 0)
    {
      $note_val = trim(substr($p, strpos($p, ':') + 1));
      $res['notes'] .= ($res['notes'] !== '' ? ' | ' : '') . $note_val;
    }
    else
    {
      $lower = mb_strtolower($p, 'UTF-8');
      $is_known_equip = (
        strpos($lower, 'proyector') !== false ||
        strpos($lower, 'táctil') !== false || strpos($lower, 'tactil') !== false ||
        strpos($lower, 'pc') !== false ||
        strpos($lower, 'audio') !== false ||
        strpos($lower, 'a/c') !== false || strpos($lower, 'aire') !== false ||
        strpos($lower, 'tv') !== false ||
        strpos($lower, 'cámara') !== false || strpos($lower, 'camara') !== false ||
        strpos($lower, 'pizarra') !== false || strpos($lower, 'microf') !== false ||
        strpos($lower, 'telón') !== false || strpos($lower, 'telon') !== false ||
        strpos($lower, 'laptop') !== false ||
        strpos($p, ',') !== false
      );

      // Si es la segunda parte y no empieza con palabras de ubicación, o detecta equipamiento
      if ($is_known_equip || ($idx === 1 && count($parts) > 1 && !preg_match('/^(piso|edificio|sector|ubicaci[oó]n|ala|bloque)/iu', $p)))
      {
        $items = array_map('trim', explode(',', $p));
        foreach ($items as $it)
        {
          if ($it === '') continue;
          $it_lower = mb_strtolower($it, 'UTF-8');
          $res['raw_equip'][] = $it;

          // Mapear a checkboxes estándar si coincide
          $matched_std = false;
          if (strpos($it_lower, 'proyector') !== false) { $res['equip']['proyector'] = true; $matched_std = true; }
          if (strpos($it_lower, 'táctil') !== false || strpos($it_lower, 'tactil') !== false) { $res['equip']['tactil'] = true; $matched_std = true; }
          if (strpos($it_lower, 'pc') !== false) { $res['equip']['pc'] = true; $matched_std = true; }
          if (strpos($it_lower, 'audio') !== false) { $res['equip']['audio'] = true; $matched_std = true; }
          if (strpos($it_lower, 'a/c') !== false || strpos($it_lower, 'aire') !== false) { $res['equip']['ac'] = true; $matched_std = true; }
          if (strpos($it_lower, 'tv') !== false) { $res['equip']['tv'] = true; $matched_std = true; }

          if (!$matched_std)
          {
            $res['custom_equip'][] = $it;
          }
        }
      }
      else
      {
        $res['notes'] .= ($res['notes'] !== '' ? ' | ' : '') . $p;
      }
    }
  }
  return $res;
}

// Renderizar cabecera institucional UdeC
$context = array(
  'view'      => $view ?? 'day',
  'view_all'  => $view_all ?? true,
  'year'      => $year ?? (int)date('Y'),
  'month'     => $month ?? (int)date('m'),
  'day'       => $day ?? (int)date('d'),
  'area'      => $area ?? null,
  'room'      => $room ?? null
);

print_header($context);
?>

<div class="udec-rooms-wrapper">

  <!-- =====================================================================
       HEADER PRINCIPAL Y CONMUTADOR A VERSIÓN CLÁSICA
       ===================================================================== -->
  <div class="udec-rooms-header">
    <div class="udec-rooms-header-left">
      <div class="udec-rooms-header-icon">
        <i class="fa-solid fa-building-circle-check"></i>
      </div>
      <div class="udec-rooms-header-titles">
        <h1>Administrador de Espacios Académicos</h1>
        <p>Catastro, programación de mantenciones y disponibilidad en tiempo real del Campus Los Ángeles</p>
      </div>
    </div>
    <div class="udec-rooms-header-actions">
      <button type="button" class="udec-btn-new-room" onclick="openRoomDrawer(null)">
        <i class="fa-solid fa-plus"></i> Nueva Sala
      </button>
      <a href="admin.php?set_view=classic" class="udec-btn-classic-switch" title="Cambiar a la interfaz tradicional de MRBS">
        <i class="fa-solid fa-arrow-rotate-left"></i> Versión Clásica
      </a>
    </div>
  </div>

  <!-- Alertas de Éxito / Error -->
  <?php if (!empty($msg_success)): ?>
    <div class="udec-toast-alert success">
      <i class="fa-solid fa-circle-check"></i>
      <span><?php echo htmlspecialchars($msg_success); ?></span>
    </div>
  <?php endif; ?>

  <?php if (!empty($msg_error)): ?>
    <div class="udec-toast-alert error">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <span><?php echo htmlspecialchars($msg_error); ?></span>
    </div>
  <?php endif; ?>

  <!-- =====================================================================
       MÉTRICAS Y KPIS EJECUTIVOS DEL CAMPUS
       ===================================================================== -->
  <div class="udec-rooms-kpis">
    <div class="udec-kpi-card">
      <div class="udec-kpi-icon blue">
        <i class="fa-solid fa-door-open"></i>
      </div>
      <div class="udec-kpi-info">
        <span class="udec-kpi-value"><?php echo $total_rooms; ?></span>
        <span class="udec-kpi-label">Salas Catastradas</span>
      </div>
    </div>

    <div class="udec-kpi-card">
      <div class="udec-kpi-icon gold">
        <i class="fa-solid fa-users"></i>
      </div>
      <div class="udec-kpi-info">
        <span class="udec-kpi-value"><?php echo number_format($total_capacity, 0, ',', '.'); ?></span>
        <span class="udec-kpi-label">Capacidad Activa (Plazas)</span>
      </div>
    </div>

    <div class="udec-kpi-card">
      <div class="udec-kpi-icon green">
        <i class="fa-solid fa-calendar-check"></i>
      </div>
      <div class="udec-kpi-info">
        <span class="udec-kpi-value"><?php echo $count_busy_now; ?></span>
        <span class="udec-kpi-label">Ocupadas en este Momento</span>
      </div>
    </div>

    <div class="udec-kpi-card">
      <div class="udec-kpi-icon amber">
        <i class="fa-solid fa-screwdriver-wrench"></i>
      </div>
      <div class="udec-kpi-info">
        <span class="udec-kpi-value"><?php echo ($count_maintenance + $count_out_of_service); ?></span>
        <span class="udec-kpi-label">
          <?php echo $count_maintenance; ?> Mant. | <?php echo $count_out_of_service; ?> Fuera Servicio
        </span>
      </div>
    </div>
  </div>

  <!-- =====================================================================
       TOOLBAR DE FILTROS EN TIEMPO REAL Y BÚSQUEDA
       ===================================================================== -->
  <div class="udec-rooms-toolbar">
    <div class="udec-toolbar-row-top">
      <div class="udec-search-input-group">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="udecRoomSearchInput" placeholder="Buscar por sala, edificio, tecnología o motivo de mantención..." autocomplete="off">
      </div>

      <select id="udecFilterArea" class="udec-toolbar-select">
        <option value="">Todos los Edificios (<?php echo count($areas); ?>)</option>
        <?php foreach ($areas as $a): ?>
          <option value="<?php echo (int)$a['id']; ?>"><?php echo htmlspecialchars($a['area_name']); ?></option>
        <?php endforeach; ?>
      </select>

      <select id="udecFilterType" class="udec-toolbar-select">
        <option value="">Todos los Tipos</option>
        <option value="Híbrida">Híbrida</option>
        <option value="AIO">AIO</option>
        <option value="Normal">Normal</option>
        <option value="Laboratorio">Laboratorio</option>
        <option value="Reuniones">Reuniones</option>
      </select>

      <select id="udecFilterStatus" class="udec-toolbar-select">
        <option value="">Todos los Estados</option>
        <option value="operativa">Operativas (Disponibles)</option>
        <option value="busy">En Uso Ahora</option>
        <option value="maintenance">En Mantención</option>
        <option value="out_of_service">Fuera de Servicio</option>
      </select>

      <div class="udec-view-toggle">
        <button type="button" class="udec-view-btn active" id="btnViewCards" onclick="setRoomsViewMode('cards')" title="Vista en Tarjetas">
          <i class="fa-solid fa-grip"></i> Tarjetas
        </button>
        <button type="button" class="udec-view-btn" id="btnViewTable" onclick="setRoomsViewMode('table')" title="Vista en Tabla">
          <i class="fa-solid fa-table-list"></i> Tabla
        </button>
      </div>
    </div>

    <!-- Fila de Chips Rápidos de Equipamiento -->
    <div class="udec-toolbar-row-chips">
      <span class="udec-chips-label"><i class="fa-solid fa-filter"></i> Equipamiento:</span>
      <span class="udec-filter-chip" data-equip-filter="proyector"><i class="fa-solid fa-video"></i> Proyector</span>
      <span class="udec-filter-chip" data-equip-filter="tactil"><i class="fa-solid fa-tablet-screen-button"></i> Pantalla Táctil</span>
      <span class="udec-filter-chip" data-equip-filter="pc"><i class="fa-solid fa-desktop"></i> PC</span>
      <span class="udec-filter-chip" data-equip-filter="audio"><i class="fa-solid fa-volume-high"></i> Audio AUX/BT</span>
      <span class="udec-filter-chip" data-equip-filter="ac"><i class="fa-solid fa-snowflake"></i> Aire Acondicionado</span>
      <span class="udec-filter-chip" data-equip-filter="tv"><i class="fa-solid fa-tv"></i> TV</span>
    </div>
  </div>

  <!-- =====================================================================
       VISTA 1: GRILLA DE TARJETAS (CARDS)
       ===================================================================== -->
  <div id="udecRoomsGrid" class="udec-rooms-grid">
    <?php foreach ($rooms as $r):
      $rid = (int)$r['id'];
      $aid = (int)$r['area_id'];
      $is_disabled_raw = ($r['disabled'] == 1 || $r['area_disabled'] == 1);
      $is_busy = isset($busy_now[$rid]);
      $today_c = $today_counts[$rid] ?? 0;
      $parsed = parse_room_description($r['description']);
      $maint = parse_room_maintenance($r['custom_html'] ?? null);

      // Evaluación del Estado
      $card_class = '';
      if ($maint['enabled'])
      {
        if ($maint['mode'] === 'fuera_de_servicio')
        {
          $status_class = 'status-out-of-service';
          $status_text  = 'Fuera de Servicio';
          $status_icon  = 'fa-ban';
          $filter_st    = 'out_of_service';
          $card_class   = 'is-out-of-service';
        }
        else
        {
          $status_class = $maint['is_future'] ? 'status-future-maint' : 'status-maintenance';
          $status_text  = $maint['is_future'] ? 'Mant. Programada' : 'En Mantención';
          $status_icon  = $maint['is_future'] ? 'fa-clock' : 'fa-screwdriver-wrench';
          $filter_st    = 'maintenance';
          $card_class   = 'is-maintenance';
        }
      }
      elseif ($is_disabled_raw)
      {
        $status_class = 'status-maintenance';
        $status_text  = 'Deshabilitada';
        $status_icon  = 'fa-ban';
        $filter_st    = 'maintenance';
        $card_class   = 'is-disabled';
      }
      elseif ($is_busy)
      {
        $status_class = 'status-busy';
        $status_text  = 'En Uso Ahora';
        $status_icon  = 'fa-circle-dot';
        $filter_st    = 'busy';
      }
      else
      {
        $status_class = 'status-free';
        $status_text  = 'Disponible';
        $status_icon  = 'fa-circle-check';
        $filter_st    = 'operativa';
      }

      // Equip tokens string for JS filtering
      $equip_keys = array_keys($parsed['equip']);
      $equip_attr = implode(' ', $equip_keys);

      // Searchable text string
      $search_text = mb_strtolower(
        $r['room_name'] . ' ' . $r['area_name'] . ' ' . ($r['description'] ?? '') . ' ' . $maint['reason'] . ' ' . $status_text,
        'UTF-8'
      );

      // JSON metadata for the drawer
      $meta_json = htmlspecialchars(json_encode(array(
        'id'           => $rid,
        'area_id'      => $aid,
        'room_name'    => $r['room_name'],
        'capacity'     => (int)$r['capacity'],
        'email'        => $r['room_admin_email'] ?? '',
        'disabled'     => (int)$r['disabled'],
        'type'         => $parsed['type'],
        'equip'        => $parsed['equip'],
        'custom_equip' => $parsed['custom_equip'],
        'raw_equip'    => $parsed['raw_equip'],
        'notes'        => $parsed['notes'],
        'description'  => $r['description'] ?? '',
        'maintenance'  => $maint
      )), ENT_QUOTES, 'UTF-8');
    ?>
      <div class="udec-room-card <?php echo $card_class; ?>"
           data-room-id="<?php echo $rid; ?>"
           data-area-id="<?php echo $aid; ?>"
           data-name="<?php echo htmlspecialchars($search_text); ?>"
           data-type="<?php echo htmlspecialchars($parsed['type']); ?>"
           data-status="<?php echo $filter_st; ?>"
           data-equip="<?php echo htmlspecialchars($equip_attr); ?>"
           data-meta="<?php echo $meta_json; ?>">

        <!-- Header Tarjeta -->
        <div class="udec-card-header">
          <div class="udec-card-title-group">
            <span class="udec-card-room-name"><?php echo htmlspecialchars($r['room_name']); ?></span>
            <span class="udec-card-area-name"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($r['area_name']); ?></span>
          </div>
          <span class="udec-card-status-badge <?php echo $status_class; ?>">
            <i class="fa-solid <?php echo $status_icon; ?>"></i> <?php echo $status_text; ?>
          </span>
        </div>

        <!-- Body Tarjeta -->
        <div class="udec-card-body">
          <div class="udec-card-metrics-row">
            <span class="udec-card-badge-cap" title="Capacidad Oficial">
              <i class="fa-solid fa-users"></i> <?php echo (int)$r['capacity']; ?> pers.
            </span>
            <span class="udec-card-badge-type">
              <i class="fa-solid fa-layer-group"></i> <?php echo htmlspecialchars($parsed['type']); ?>
            </span>
          </div>

          <!-- Tags de Equipamiento -->
          <div class="udec-card-equip-list">
            <?php if (!empty($parsed['equip']['proyector'])): ?>
              <span class="udec-equip-tag"><i class="fa-solid fa-video"></i> Proyector</span>
            <?php endif; ?>
            <?php if (!empty($parsed['equip']['tactil'])): ?>
              <span class="udec-equip-tag"><i class="fa-solid fa-tablet-screen-button"></i> Táctil</span>
            <?php endif; ?>
            <?php if (!empty($parsed['equip']['pc'])): ?>
              <span class="udec-equip-tag"><i class="fa-solid fa-desktop"></i> PC</span>
            <?php endif; ?>
            <?php if (!empty($parsed['equip']['audio'])): ?>
              <span class="udec-equip-tag"><i class="fa-solid fa-volume-high"></i> Audio</span>
            <?php endif; ?>
            <?php if (!empty($parsed['equip']['ac'])): ?>
              <span class="udec-equip-tag"><i class="fa-solid fa-snowflake"></i> A/C</span>
            <?php endif; ?>
            <?php if (!empty($parsed['equip']['tv'])): ?>
              <span class="udec-equip-tag"><i class="fa-solid fa-tv"></i> TV</span>
            <?php endif; ?>
            <?php if (!empty($parsed['custom_equip'])): ?>
              <?php foreach ($parsed['custom_equip'] as $c_eq): ?>
                <span class="udec-equip-tag udec-equip-custom" title="Equipamiento adicional"><i class="fa-solid fa-plug-circle-check" style="color: #0284C7;"></i> <?php echo htmlspecialchars($c_eq); ?></span>
              <?php endforeach; ?>
            <?php endif; ?>
            <?php if (empty($parsed['raw_equip']) && !empty($parsed['notes'])): ?>
              <span class="udec-equip-tag"><i class="fa-solid fa-circle-info"></i> <?php echo htmlspecialchars($parsed['notes']); ?></span>
            <?php endif; ?>
          </div>

          <!-- Cuadro Destacado de Mantención / Fuera de Servicio -->
          <?php if ($maint['enabled']): ?>
            <div class="udec-card-maint-box <?php echo ($maint['mode'] === 'fuera_de_servicio') ? 'out-of-service' : ''; ?>">
              <div class="udec-maint-box-header">
                <i class="fa-solid <?php echo ($maint['mode'] === 'fuera_de_servicio') ? 'fa-ban' : 'fa-screwdriver-wrench'; ?>"></i>
                <strong><?php echo ($maint['mode'] === 'fuera_de_servicio') ? 'Fuera de Servicio' : 'En Mantención'; ?></strong>
                <?php if ($maint['is_future']): ?>
                  <span class="udec-maint-pill-future">Programada</span>
                <?php endif; ?>
              </div>

              <?php if (!empty($maint['from']) || !empty($maint['to'])): ?>
                <div class="udec-maint-box-period">
                  <i class="fa-regular fa-clock"></i>
                  <span>
                    <?php
                      $f_txt = !empty($maint['from']) ? date('d/m/Y H:i', strtotime($maint['from'])) : '—';
                      $t_txt = !empty($maint['to'])   ? date('d/m/Y H:i', strtotime($maint['to']))   : 'Indefinido';
                      echo "$f_txt &bull; $t_txt";
                    ?>
                  </span>
                </div>
              <?php endif; ?>

              <?php if (!empty($maint['reason'])): ?>
                <div class="udec-maint-box-reason">
                  <i class="fa-solid fa-circle-info"></i>
                  <span><?php echo htmlspecialchars($maint['reason']); ?></span>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <!-- Indicador de Ocupación Hoy -->
          <div class="udec-card-today-info">
            <i class="fa-regular fa-clock"></i>
            <span>
              <?php if ($today_c > 0): ?>
                <strong><?php echo $today_c; ?></strong> reserva(s) programada(s) hoy
              <?php else: ?>
                Sin reservas registradas para hoy
              <?php endif; ?>
            </span>
          </div>
        </div>

        <!-- Footer Tarjeta con Acciones -->
        <div class="udec-card-footer">
          <a href="<?php echo htmlspecialchars(multisite("index.php?view=day&view_all=0&page_date=" . date('Y-m-d') . "&area=" . $aid . "&room=" . $rid)); ?>"
             class="udec-card-btn-action" title="Ver programación de hoy de esta sala" target="_blank">
            <i class="fa-regular fa-calendar-days"></i> Ver Horario
          </a>

          <div style="display: flex; gap: 6px;">
            <?php if ($maint['enabled'] || $is_disabled_raw): ?>
              <!-- Botón Reactivar Sala (1 Clic) -->
              <form method="post" action="admin_rooms.php" style="display:inline; margin:0;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(Form::getToken()); ?>">
                <input type="hidden" name="action" value="clear_maintenance">
                <input type="hidden" name="room_id" value="<?php echo $rid; ?>">
                <button type="submit" class="udec-card-btn-action udec-btn-reactivate" title="Finalizar mantención y reactivar sala">
                  <i class="fa-solid fa-power-off"></i> Reactivar
                </button>
              </form>
            <?php else: ?>
              <!-- Botón rápido para abrir modal en sección mantención -->
              <button type="button" class="udec-card-btn-action" title="Poner en Mantención / Fuera de Servicio" onclick="openRoomDrawer(this.closest('.udec-room-card'), true)">
                <i class="fa-solid fa-screwdriver-wrench"></i> Mantención
              </button>
            <?php endif; ?>

            <!-- Botón Editar -->
            <button type="button" class="udec-card-btn-action udec-card-btn-edit" onclick="openRoomDrawer(this.closest('.udec-room-card'))">
              <i class="fa-solid fa-pen-to-square"></i> Editar
            </button>
          </div>
        </div>

      </div>
    <?php endforeach; ?>
  </div>

  <!-- =====================================================================
       VISTA 2: TABLA COMPACTA EJECUTIVA (Alternativa)
       ===================================================================== -->
  <div id="udecRoomsTableContainer" class="udec-rooms-table-container" style="display: none;">
    <table class="udec-modern-table" id="udecRoomsTable">
      <thead>
        <tr>
          <th>Recinto</th>
          <th>Edificio / Área</th>
          <th>Aforo</th>
          <th>Tipo</th>
          <th>Equipamiento</th>
          <th>Estado y Mantención</th>
          <th style="text-align: right;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rooms as $r):
          $rid = (int)$r['id'];
          $aid = (int)$r['area_id'];
          $is_disabled_raw = ($r['disabled'] == 1 || $r['area_disabled'] == 1);
          $is_busy = isset($busy_now[$rid]);
          $today_c = $today_counts[$rid] ?? 0;
          $parsed = parse_room_description($r['description']);
          $maint = parse_room_maintenance($r['custom_html'] ?? null);

          if ($maint['enabled'])
          {
            if ($maint['mode'] === 'fuera_de_servicio')
            {
              $st_badge = '<span class="udec-card-status-badge status-out-of-service"><i class="fa-solid fa-ban"></i> Fuera de Servicio</span>';
              $filter_st = 'out_of_service';
            }
            else
            {
              $st_badge = '<span class="udec-card-status-badge status-maintenance"><i class="fa-solid fa-screwdriver-wrench"></i> Mantención</span>';
              $filter_st = 'maintenance';
            }
          }
          elseif ($is_disabled_raw)
          {
            $st_badge = '<span class="udec-card-status-badge status-maintenance"><i class="fa-solid fa-screwdriver-wrench"></i> Deshabilitada</span>';
            $filter_st = 'maintenance';
          }
          elseif ($is_busy)
          {
            $st_badge = '<span class="udec-card-status-badge status-busy"><i class="fa-solid fa-circle-dot"></i> En Uso</span>';
            $filter_st = 'busy';
          }
          else
          {
            $st_badge = '<span class="udec-card-status-badge status-free"><i class="fa-solid fa-circle-check"></i> Disponible</span>';
            $filter_st = 'operativa';
          }

          $equip_keys = array_keys($parsed['equip']);
          $equip_attr = implode(' ', $equip_keys);
          $search_text = mb_strtolower(
            $r['room_name'] . ' ' . $r['area_name'] . ' ' . ($r['description'] ?? '') . ' ' . $maint['reason'],
            'UTF-8'
          );

          $meta_json = htmlspecialchars(json_encode(array(
            'id'           => $rid,
            'area_id'      => $aid,
            'room_name'    => $r['room_name'],
            'capacity'     => (int)$r['capacity'],
            'email'        => $r['room_admin_email'] ?? '',
            'disabled'     => (int)$r['disabled'],
            'type'         => $parsed['type'],
            'equip'        => $parsed['equip'],
            'custom_equip' => $parsed['custom_equip'],
            'raw_equip'    => $parsed['raw_equip'],
            'notes'        => $parsed['notes'],
            'description'  => $r['description'] ?? '',
            'maintenance'  => $maint
          )), ENT_QUOTES, 'UTF-8');
        ?>
          <tr data-room-id="<?php echo $rid; ?>"
              data-area-id="<?php echo $aid; ?>"
              data-name="<?php echo htmlspecialchars($search_text); ?>"
              data-type="<?php echo htmlspecialchars($parsed['type']); ?>"
              data-status="<?php echo $filter_st; ?>"
              data-equip="<?php echo htmlspecialchars($equip_attr); ?>"
              data-meta="<?php echo $meta_json; ?>">
            <td><strong><?php echo htmlspecialchars($r['room_name']); ?></strong></td>
            <td><?php echo htmlspecialchars($r['area_name']); ?></td>
            <td><i class="fa-solid fa-users" style="color: #E89D1E;"></i> <?php echo (int)$r['capacity']; ?></td>
            <td><span class="udec-card-badge-type"><?php echo htmlspecialchars($parsed['type']); ?></span></td>
            <td>
              <span style="font-size: 12px; color: #475569;">
                <?php echo !empty($parsed['raw_equip']) ? htmlspecialchars(implode(', ', $parsed['raw_equip'])) : '—'; ?>
              </span>
            </td>
            <td>
              <div style="display: flex; flex-direction: column; gap: 4px;">
                <?php echo $st_badge; ?>
                <?php if ($maint['enabled'] && (!empty($maint['from']) || !empty($maint['to']))): ?>
                  <span style="font-size: 11px; color: #78350F; font-weight: 500;">
                    <?php
                      $f_t = !empty($maint['from']) ? date('d/m/Y H:i', strtotime($maint['from'])) : '—';
                      $t_t = !empty($maint['to'])   ? date('d/m/Y H:i', strtotime($maint['to']))   : 'Indef.';
                      echo "$f_t a $t_t";
                    ?>
                  </span>
                <?php endif; ?>
              </div>
            </td>
            <td style="text-align: right; white-space: nowrap;">
              <a href="<?php echo htmlspecialchars(multisite("index.php?view=day&view_all=0&page_date=" . date('Y-m-d') . "&area=" . $aid . "&room=" . $rid)); ?>"
                 class="udec-card-btn-action" title="Ver horario" target="_blank" style="margin-right: 4px;">
                <i class="fa-regular fa-calendar-days"></i>
              </a>
              <?php if ($maint['enabled'] || $is_disabled_raw): ?>
                <form method="post" action="admin_rooms.php" style="display:inline; margin:0 4px 0 0;">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(Form::getToken()); ?>">
                  <input type="hidden" name="action" value="clear_maintenance">
                  <input type="hidden" name="room_id" value="<?php echo $rid; ?>">
                  <button type="submit" class="udec-card-btn-action udec-btn-reactivate" title="Finalizar mantención y reactivar sala">
                    <i class="fa-solid fa-power-off"></i> Reactivar
                  </button>
                </form>
              <?php endif; ?>
              <button type="button" class="udec-card-btn-action udec-card-btn-edit" onclick="openRoomDrawer(this.closest('tr'))">
                <i class="fa-solid fa-pen-to-square"></i> Editar
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- =========================================================================
     DRAWER OFFCANVAS: EDICIÓN Y CREACIÓN RÁPIDA DE SALAS
     ========================================================================= -->
<div id="udecDrawerBackdrop" class="udec-drawer-backdrop" onclick="closeRoomDrawer()"></div>

<div id="udecRoomDrawer" class="udec-drawer">
  <div class="udec-drawer-header">
    <h3 id="udecDrawerTitle"><i class="fa-solid fa-door-open"></i> Editar Recinto</h3>
    <button type="button" class="udec-drawer-close" onclick="closeRoomDrawer()">&times;</button>
  </div>

  <form method="post" action="admin_rooms.php" id="udecRoomForm" style="display: flex; flex-direction: column; flex: 1; overflow: hidden; margin: 0;">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(Form::getToken()); ?>">
    <input type="hidden" name="action" value="save_room">
    <input type="hidden" name="room_id" id="drawerRoomId" value="0">

    <div class="udec-drawer-body">

      <!-- Sección 1: Identificación y Ubicación -->
      <div class="udec-form-section">
        <span class="udec-form-section-title"><i class="fa-solid fa-location-dot"></i> Ubicación y Nombre</span>

        <div class="udec-form-group">
          <label for="drawerAreaId">Edificio / Área *</label>
          <select name="area_id" id="drawerAreaId" required>
            <?php foreach ($areas as $a): ?>
              <option value="<?php echo (int)$a['id']; ?>"><?php echo htmlspecialchars($a['area_name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="udec-form-group">
          <label for="drawerRoomName">Nombre Oficial del Recinto *</label>
          <input type="text" name="room_name" id="drawerRoomName" placeholder="Ej: Sala 15, Lab 1, Auditorio" required maxlength="25">
        </div>
      </div>

      <!-- Sección 2: Capacidad y Tipo de Espacio -->
      <div class="udec-form-section">
        <span class="udec-form-section-title"><i class="fa-solid fa-users"></i> Capacidad y Categoría</span>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
          <div class="udec-form-group">
            <label for="drawerCapacity">Capacidad (Plazas) *</label>
            <input type="number" name="capacity" id="drawerCapacity" min="0" max="999" value="40" required>
          </div>

          <div class="udec-form-group">
            <label for="drawerRoomType">Tipo de Espacio</label>
            <select name="room_type" id="drawerRoomType">
              <option value="Normal">Normal</option>
              <option value="AIO">AIO</option>
              <option value="Híbrida">Híbrida</option>
              <option value="Laboratorio">Laboratorio</option>
              <option value="Reuniones">Reuniones</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Sección 3: Equipamiento Oficial (Switches / Toggles) -->
      <div class="udec-form-section">
        <span class="udec-form-section-title"><i class="fa-solid fa-plug-circle-check"></i> Equipamiento y Tecnología Activa</span>
        <div class="udec-grid-switches">
          <label class="udec-switch-item">
            <input type="checkbox" name="equip_proyector" id="drawerEquipProyector" value="1">
            <span><i class="fa-solid fa-video" style="color: #0284C7;"></i> Proyector</span>
          </label>
          <label class="udec-switch-item">
            <input type="checkbox" name="equip_tactil" id="drawerEquipTactil" value="1">
            <span><i class="fa-solid fa-tablet-screen-button" style="color: #0284C7;"></i> Pantalla Táctil</span>
          </label>
          <label class="udec-switch-item">
            <input type="checkbox" name="equip_pc" id="drawerEquipPc" value="1">
            <span><i class="fa-solid fa-desktop" style="color: #0284C7;"></i> PC Docente</span>
          </label>
          <label class="udec-switch-item">
            <input type="checkbox" name="equip_audio" id="drawerEquipAudio" value="1">
            <span><i class="fa-solid fa-volume-high" style="color: #0284C7;"></i> Audio AUX/BT</span>
          </label>
          <label class="udec-switch-item">
            <input type="checkbox" name="equip_ac" id="drawerEquipAc" value="1">
            <span><i class="fa-solid fa-snowflake" style="color: #0284C7;"></i> Climatización A/C</span>
          </label>
          <label class="udec-switch-item">
            <input type="checkbox" name="equip_tv" id="drawerEquipTv" value="1">
            <span><i class="fa-solid fa-tv" style="color: #0284C7;"></i> Televisor (TV)</span>
          </label>
        </div>

        <!-- Gestor Dinámico de Equipamiento Específico / Personalizado -->
        <div class="udec-custom-equip-manager" style="margin-top: 14px; background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 8px; padding: 12px;">
          <label style="font-size: 12.5px; font-weight: 700; color: #002B49; display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
            <span><i class="fa-solid fa-plus-circle" style="color: #0284C7;"></i> Equipamiento Específico o Personalizado</span>
            <span style="font-weight: 400; font-size: 11px; color: #64748B;">Presiona Enter o botón para añadir</span>
          </label>

          <div style="display: flex; gap: 8px;">
            <input type="text" id="drawerCustomEquipInput" placeholder="Ej: Cámara PTZ, Microfonía Inalámbrica, 30 Laptops..." style="flex: 1; font-size: 13px; padding: 7px 11px; border: 1.5px solid #CBD5E1; border-radius: 6px;">
            <button type="button" class="udec-btn-add-equip" onclick="addCustomEquipTag()" style="background: #0284C7; color: #FFFFFF; border: none; padding: 7px 14px; border-radius: 6px; font-weight: 700; font-size: 12.5px; cursor: pointer; white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;">
              <i class="fa-solid fa-plus"></i> Añadir
            </button>
          </div>

          <!-- Sugerencias Rápidas de Equipamiento Frecuente -->
          <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 8px;">
            <span style="font-size: 11px; color: #64748B; font-weight: 600;">Sugerencias:</span>
            <button type="button" class="udec-equip-suggestion" onclick="addCustomEquipFromSuggestion('Cámara PTZ')">+ Cámara PTZ</button>
            <button type="button" class="udec-equip-suggestion" onclick="addCustomEquipFromSuggestion('Microfonía Inalámbrica')">+ Microfonía Inalámbrica</button>
            <button type="button" class="udec-equip-suggestion" onclick="addCustomEquipFromSuggestion('Pizarra Interactiva')">+ Pizarra Interactiva</button>
            <button type="button" class="udec-equip-suggestion" onclick="addCustomEquipFromSuggestion('Telón Motorizado')">+ Telón Motorizado</button>
            <button type="button" class="udec-equip-suggestion" onclick="addCustomEquipFromSuggestion('Pizarra Acrílica')">+ Pizarra Acrílica</button>
            <button type="button" class="udec-equip-suggestion" onclick="addCustomEquipFromSuggestion('Mesas Modulares')">+ Mesas Modulares</button>
          </div>

          <!-- Contenedor de Tags Agregados -->
          <div id="drawerCustomEquipTags" style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; min-height: 20px;"></div>
          <input type="hidden" name="custom_equip" id="drawerCustomEquipHidden" value="">
        </div>

        <div class="udec-form-group" style="margin-top: 10px;">
          <label for="drawerNotes">Observaciones Adicionales / Ubicación Física</label>
          <input type="text" name="notes" id="drawerNotes" placeholder="Ej: Piso 2, Pabellón Central, Ala Sur..." maxlength="80">
        </div>
      </div>

      <!-- Sección 4: MANTENCIÓN / FUERA DE SERVICIO (CON FECHA Y HORA) -->
      <div class="udec-form-section" id="sectionMaintenance" style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 10px; padding: 14px; transition: all 0.25s ease;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
          <span class="udec-form-section-title" style="margin-bottom: 0; border-bottom: none; color: #002B49;">
            <i class="fa-solid fa-screwdriver-wrench"></i> Fuera de Servicio / En Mantención
          </span>
          <span id="drawerMaintStatusBadge" class="udec-status-pill pill-green">
            <i class="fa-solid fa-circle-check"></i> Sala Operativa
          </span>
        </div>

        <!-- Switch Principal con botón rápido para quitar mantención -->
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; background: #FFFFFF; border: 1.5px solid #CBD5E1; border-radius: 8px; padding: 10px 14px;">
          <input type="hidden" name="maint_enabled" id="drawerMaintEnabledVal" value="0">
          <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin: 0; user-select: none; flex: 1;">
            <input type="checkbox" id="drawerMaintEnabled" value="1" onchange="toggleMaintFields()" style="width: 20px; height: 20px; cursor: pointer; accent-color: #D97706;">
            <span id="drawerMaintSwitchLabel" style="font-weight: 700; color: #1E293B; font-size: 13px;">
              Activar Mantención o Fuera de Servicio
            </span>
          </label>
          <button type="button" id="btnDrawerClearMaint" class="udec-btn-clear-maint-fast" onclick="directReactivateInDrawer()" style="display: none;" title="Quitar mantención y reactivar sala inmediatamente">
            <i class="fa-solid fa-power-off"></i> Quitar Mantención y Reactivar
          </button>
        </div>

        <!-- Campos Desplegables de Mantención -->
        <div id="drawerMaintFields" style="display: none; margin-top: 12px; flex-direction: column; gap: 12px;">
          
          <!-- Tipo de Intervención -->
          <div class="udec-form-group">
            <label>Tipo de Intervención *</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
              <label class="udec-radio-card" id="lblModeMant">
                <input type="radio" name="maint_mode" id="radioModeMant" value="mantencion" checked onchange="updateMaintTheme()">
                <span><i class="fa-solid fa-screwdriver-wrench" style="color: #D97706;"></i> Mantención</span>
              </label>
              <label class="udec-radio-card" id="lblModeFuera">
                <input type="radio" name="maint_mode" id="radioModeFuera" value="fuera_de_servicio" onchange="updateMaintTheme()">
                <span><i class="fa-solid fa-ban" style="color: #DC2626;"></i> Fuera de Servicio</span>
              </label>
            </div>
          </div>

          <!-- Rango Desde / Hasta con Fecha y Hora -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div class="udec-form-group">
              <label for="drawerMaintFrom"><i class="fa-regular fa-calendar-days"></i> Desde (Fecha y Hora) *</label>
              <input type="datetime-local" name="maint_from" id="drawerMaintFrom">
            </div>

            <div class="udec-form-group">
              <label for="drawerMaintTo"><i class="fa-regular fa-calendar-check"></i> Hasta (Fecha y Hora) *</label>
              <input type="datetime-local" name="maint_to" id="drawerMaintTo">
            </div>
          </div>

          <!-- Motivo de la Mantención -->
          <div class="udec-form-group">
            <label for="drawerMaintReason"><i class="fa-solid fa-circle-info"></i> Motivo o Causa (Se mostrará en la grilla del horario) *</label>
            <input type="text" name="maint_reason" id="drawerMaintReason" placeholder="Ej: Reparación eléctrica, cambio de proyector, pintura..." maxlength="120">
          </div>

          <div style="font-size: 11.5px; color: #78350F; background: #FEF3C7; padding: 8px 12px; border-radius: 6px; line-height: 1.4; border-left: 3px solid #D97706;">
            <i class="fa-solid fa-shield-halved"></i> <strong>Aviso:</strong> La sala <strong>no desaparecerá</strong> del calendario. Se mostrará en los horarios durante la fecha seleccionada con un bloque visible que indicará claramente el <strong>motivo</strong> ingresado.
          </div>
        </div>

      </div>

      <!-- Sección 5: Correo Administrador -->
      <div class="udec-form-section">
        <span class="udec-form-section-title"><i class="fa-solid fa-envelope"></i> Contacto y Supervisión</span>
        <div class="udec-form-group">
          <label for="drawerEmail">Correo Administrador / Encargado del Recinto (Opcional)</label>
          <input type="email" name="room_admin_email" id="drawerEmail" placeholder="encargado@udec.cl">
        </div>
      </div>

    </div>

    <!-- Footer con Botones -->
    <div class="udec-drawer-footer">
      <button type="button" class="udec-btn-cancel" onclick="closeRoomDrawer()">Cancelar</button>
      <button type="submit" class="udec-btn-save"><i class="fa-solid fa-floppy-disk"></i> Guardar Recinto</button>
    </div>
  </form>
</div>

<!-- =========================================================================
     JAVASCRIPT INTERACTIVO: FILTROS CLIENT-SIDE + CONTROL DE DRAWER
     ========================================================================= -->
<script>
(function() {
  'use strict';

  // Elementos DOM
  const searchInput  = document.getElementById('udecRoomSearchInput');
  const filterArea   = document.getElementById('udecFilterArea');
  const filterType   = document.getElementById('udecFilterType');
  const filterStatus = document.getElementById('udecFilterStatus');
  const equipChips   = document.querySelectorAll('.udec-filter-chip');

  const cardsContainer = document.getElementById('udecRoomsGrid');
  const tableContainer = document.getElementById('udecRoomsTableContainer');

  let activeEquipFilters = new Set();

  // 1. Filtrado en tiempo real (< 1 ms de respuesta)
  function applyFilters() {
    const query = (searchInput.value || '').trim().toLowerCase();
    const areaId = filterArea.value;
    const roomType = filterType.value;
    const status = filterStatus.value;

    const cards = cardsContainer.querySelectorAll('.udec-room-card');
    const rows  = tableContainer.querySelectorAll('tbody tr');

    function checkItem(el) {
      if (query && !el.dataset.name.includes(query)) {
        return false;
      }
      if (areaId && el.dataset.areaId !== areaId) {
        return false;
      }
      if (roomType && el.dataset.type !== roomType) {
        return false;
      }
      if (status && el.dataset.status !== status) {
        return false;
      }
      if (activeEquipFilters.size > 0) {
        const itemEquip = (el.dataset.equip || '').split(' ');
        for (let ef of activeEquipFilters) {
          if (!itemEquip.includes(ef)) {
            return false;
          }
        }
      }
      return true;
    }

    cards.forEach(card => {
      card.style.display = checkItem(card) ? '' : 'none';
    });

    rows.forEach(row => {
      row.style.display = checkItem(row) ? '' : 'none';
    });
  }

  searchInput.addEventListener('input', applyFilters);
  filterArea.addEventListener('change', applyFilters);
  filterType.addEventListener('change', applyFilters);
  filterStatus.addEventListener('change', applyFilters);

  equipChips.forEach(chip => {
    chip.addEventListener('click', function() {
      const eq = this.dataset.equipFilter;
      if (activeEquipFilters.has(eq)) {
        activeEquipFilters.delete(eq);
        this.classList.remove('active');
      } else {
        activeEquipFilters.add(eq);
        this.classList.add('active');
      }
      applyFilters();
    });
  });

  // 2. Conmutador de Vistas (Cards vs Tabla) con persistencia en localStorage
  window.setRoomsViewMode = function(mode) {
    const btnCards = document.getElementById('btnViewCards');
    const btnTable = document.getElementById('btnViewTable');

    if (mode === 'table') {
      cardsContainer.style.display = 'none';
      tableContainer.style.display = 'block';
      btnTable.classList.add('active');
      btnCards.classList.remove('active');
      localStorage.setItem('udec_room_view_mode', 'table');
    } else {
      cardsContainer.style.display = 'grid';
      tableContainer.style.display = 'none';
      btnCards.classList.add('active');
      btnTable.classList.remove('active');
      localStorage.setItem('udec_room_view_mode', 'cards');
    }
  };

  const savedMode = localStorage.getItem('udec_room_view_mode');
  if (savedMode === 'table') {
    setRoomsViewMode('table');
  }

  // 3. Control del Drawer Offcanvas
  const drawer = document.getElementById('udecRoomDrawer');
  const backdrop = document.getElementById('udecDrawerBackdrop');
  const drawerTitle = document.getElementById('udecDrawerTitle');

  const fRoomId   = document.getElementById('drawerRoomId');
  const fAreaId   = document.getElementById('drawerAreaId');
  const fName     = document.getElementById('drawerRoomName');
  const fCapacity = document.getElementById('drawerCapacity');
  const fType     = document.getElementById('drawerRoomType');
  const fNotes    = document.getElementById('drawerNotes');
  const fEmail    = document.getElementById('drawerEmail');

  const chkProyector = document.getElementById('drawerEquipProyector');
  const chkTactil    = document.getElementById('drawerEquipTactil');
  const chkPc        = document.getElementById('drawerEquipPc');
  const chkAudio     = document.getElementById('drawerEquipAudio');
  const chkAc        = document.getElementById('drawerEquipAc');
  const chkTv        = document.getElementById('drawerEquipTv');

  // Mantención DOM
  const chkMaintEnabled = document.getElementById('drawerMaintEnabled');
  const pnlMaintFields  = document.getElementById('drawerMaintFields');
  const radioModeMant   = document.getElementById('radioModeMant');
  const radioModeFuera  = document.getElementById('radioModeFuera');
  const fMaintFrom      = document.getElementById('drawerMaintFrom');
  const fMaintTo        = document.getElementById('drawerMaintTo');
  const fMaintReason    = document.getElementById('drawerMaintReason');

  const secMaint        = document.getElementById('sectionMaintenance');
  const badgeMaintSt    = document.getElementById('drawerMaintStatusBadge');
  const btnClearMaint   = document.getElementById('btnDrawerClearMaint');
  const lblMaintSwitch  = document.getElementById('drawerMaintSwitchLabel');

  // Gestor de Equipamiento Dinámico / Personalizado
  const inputCustomEquip = document.getElementById('drawerCustomEquipInput');
  const containerCustomEquipTags = document.getElementById('drawerCustomEquipTags');
  const hiddenCustomEquip = document.getElementById('drawerCustomEquipHidden');
  let currentCustomEquip = [];

  function renderCustomEquipTags() {
    if (!containerCustomEquipTags) return;
    containerCustomEquipTags.innerHTML = '';
    currentCustomEquip.forEach((item, index) => {
      const tag = document.createElement('span');
      tag.className = 'udec-custom-tag-chip';
      tag.innerHTML = `
        <i class="fa-solid fa-tag" style="font-size: 10px;"></i>
        <span>${$('<div>').text(item).html()}</span>
        <button type="button" class="tag-remove-btn" onclick="removeCustomEquipTag(${index})" title="Quitar">&times;</button>
      `;
      containerCustomEquipTags.appendChild(tag);
    });
    if (hiddenCustomEquip) {
      hiddenCustomEquip.value = currentCustomEquip.join(', ');
    }
  }

  window.addCustomEquipTag = function() {
    if (!inputCustomEquip) return;
    const val = (inputCustomEquip.value || '').trim();
    if (val) {
      const parts = val.split(',').map(s => s.trim()).filter(Boolean);
      parts.forEach(p => {
        if (!currentCustomEquip.some(existing => existing.toLowerCase() === p.toLowerCase())) {
          currentCustomEquip.push(p);
        }
      });
      renderCustomEquipTags();
      inputCustomEquip.value = '';
    }
    inputCustomEquip.focus();
  };

  window.addCustomEquipFromSuggestion = function(name) {
    if (!currentCustomEquip.some(existing => existing.toLowerCase() === name.toLowerCase())) {
      currentCustomEquip.push(name);
      renderCustomEquipTags();
    }
  };

  window.removeCustomEquipTag = function(index) {
    if (index >= 0 && index < currentCustomEquip.length) {
      currentCustomEquip.splice(index, 1);
      renderCustomEquipTags();
    }
  };

  if (inputCustomEquip) {
    inputCustomEquip.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        addCustomEquipTag();
      }
    });
  }

  window.updateMaintTheme = function() {
    const isFuera = radioModeFuera.checked;
    if (chkMaintEnabled.checked) {
      if (isFuera) {
        secMaint.style.background = '#FEF2F2';
        secMaint.style.borderColor = '#FECACA';
        badgeMaintSt.className = 'udec-status-pill pill-red';
        badgeMaintSt.innerHTML = '<i class="fa-solid fa-ban"></i> Fuera de Servicio';
        lblMaintSwitch.innerText = 'Estado: Fuera de Servicio (Desmarcar para quitar)';
      } else {
        secMaint.style.background = '#FFFBEB';
        secMaint.style.borderColor = '#FDE68A';
        badgeMaintSt.className = 'udec-status-pill pill-amber';
        badgeMaintSt.innerHTML = '<i class="fa-solid fa-screwdriver-wrench"></i> En Mantención';
        lblMaintSwitch.innerText = 'Estado: En Mantención (Desmarcar para quitar)';
      }
      btnClearMaint.style.display = 'inline-flex';
    } else {
      secMaint.style.background = '#F8FAFC';
      secMaint.style.borderColor = '#E2E8F0';
      badgeMaintSt.className = 'udec-status-pill pill-green';
      badgeMaintSt.innerHTML = '<i class="fa-solid fa-circle-check"></i> Sala Operativa';
      lblMaintSwitch.innerText = 'Activar Mantención o Fuera de Servicio';
      btnClearMaint.style.display = 'none';
    }
  };

  window.directReactivateInDrawer = function() {
    const roomId = parseInt(fRoomId.value, 10);
    if (roomId > 0) {
      if (confirm('¿Desea reactivar esta sala ahora? Se eliminarán los bloqueos de mantención y la sala quedará 100% disponible para reservas.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'admin_rooms.php';
        form.innerHTML = `
          <input type="hidden" name="csrf_token" value="${document.querySelector('#udecRoomForm input[name=csrf_token]').value}">
          <input type="hidden" name="action" value="clear_maintenance">
          <input type="hidden" name="room_id" value="${roomId}">
        `;
        document.body.appendChild(form);
        form.submit();
      }
    } else {
      clearMaintenanceInDrawer();
    }
  };

  window.clearMaintenanceInDrawer = function() {
    chkMaintEnabled.checked = false;
    const hVal = document.getElementById('drawerMaintEnabledVal');
    if (hVal) hVal.value = '0';
    pnlMaintFields.style.display = 'none';
    fMaintFrom.value = '';
    fMaintTo.value = '';
    fMaintReason.value = '';
    radioModeMant.checked = true;
    updateMaintTheme();
  };

  window.toggleMaintFields = function() {
    const hVal = document.getElementById('drawerMaintEnabledVal');
    if (hVal) hVal.value = chkMaintEnabled.checked ? '1' : '0';
    if (chkMaintEnabled.checked) {
      pnlMaintFields.style.display = 'flex';
      if (!fMaintFrom.value) {
        const now = new Date();
        const pad = n => n < 10 ? '0' + n : n;
        fMaintFrom.value = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:00`;
      }
    } else {
      pnlMaintFields.style.display = 'none';
    }
    updateMaintTheme();
  };

  window.openRoomDrawer = function(sourceElement, focusMaintenance = false) {
    if (!sourceElement) {
      // Modo Nueva Sala
      drawerTitle.innerHTML = '<i class="fa-solid fa-plus"></i> Nueva Sala o Recinto';
      fRoomId.value = '0';
      fName.value = '';
      fCapacity.value = '40';
      fType.value = 'Normal';
      fNotes.value = '';
      fEmail.value = '';

      chkProyector.checked = false;
      chkTactil.checked = false;
      chkPc.checked = false;
      chkAudio.checked = false;
      chkAc.checked = false;
      chkTv.checked = false;

      currentCustomEquip = [];
      renderCustomEquipTags();

      clearMaintenanceInDrawer();
    } else {
      // Modo Editar Sala
      const meta = JSON.parse(sourceElement.dataset.meta);
      drawerTitle.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Editar: ' + $('<div>').text(meta.room_name).html();
      fRoomId.value = meta.id;
      fAreaId.value = meta.area_id;
      fName.value = meta.room_name;
      fCapacity.value = meta.capacity;
      fType.value = meta.type || 'Normal';
      fNotes.value = meta.notes || '';
      fEmail.value = meta.email || '';

      chkProyector.checked = !!(meta.equip && meta.equip.proyector);
      chkTactil.checked    = !!(meta.equip && meta.equip.tactil);
      chkPc.checked        = !!(meta.equip && meta.equip.pc);
      chkAudio.checked     = !!(meta.equip && meta.equip.audio);
      chkAc.checked        = !!(meta.equip && meta.equip.ac);
      chkTv.checked        = !!(meta.equip && meta.equip.tv);

      currentCustomEquip = Array.isArray(meta.custom_equip) ? [...meta.custom_equip] : [];
      renderCustomEquipTags();

      // Cargar datos de mantención
      const maint = meta.maintenance || {};
      const isMaintActive = !!(maint && maint.enabled);

      if (isMaintActive || focusMaintenance) {
        chkMaintEnabled.checked = true;
        const hVal = document.getElementById('drawerMaintEnabledVal');
        if (hVal) hVal.value = '1';
        pnlMaintFields.style.display = 'flex';
        fMaintFrom.value = maint.from || '';
        fMaintTo.value   = maint.to || '';
        fMaintReason.value = maint.reason || '';

        if (maint.mode === 'fuera_de_servicio') {
          radioModeFuera.checked = true;
        } else {
          radioModeMant.checked = true;
        }

        if (focusMaintenance && !fMaintFrom.value) {
          const now = new Date();
          const pad = n => n < 10 ? '0' + n : n;
          fMaintFrom.value = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:00`;
        }
        updateMaintTheme();
      } else {
        clearMaintenanceInDrawer();
      }
    }

    drawer.classList.add('active');
    backdrop.classList.add('active');
    document.body.style.overflow = 'hidden';

    if (focusMaintenance) {
      setTimeout(() => {
        document.getElementById('sectionMaintenance').scrollIntoView({ behavior: 'smooth' });
        fMaintReason.focus();
      }, 300);
    }
  };

  const roomForm = document.getElementById('udecRoomForm');
  if (roomForm) {
    roomForm.addEventListener('submit', function(e) {
      if (chkMaintEnabled.checked && !fMaintReason.value.trim()) {
        e.preventDefault();
        alert('Por favor ingrese el motivo o causa de la mantención para que se muestre en la grilla.');
        fMaintReason.focus();
        return false;
      }
    });
  }

  window.closeRoomDrawer = function() {
    drawer.classList.remove('active');
    backdrop.classList.remove('active');
    document.body.style.overflow = '';
  };

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && drawer.classList.contains('active')) {
      closeRoomDrawer();
    }
  });

})();
</script>

<?php
output_trailer();
