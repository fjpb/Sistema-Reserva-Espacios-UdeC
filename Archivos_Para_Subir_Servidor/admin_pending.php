<?php
declare(strict_types=1);
namespace MRBS;

use MRBS\Form\Form;

require "defaultincludes.inc";
require_once "mrbs_sql.inc";
require_once "functions_table.inc";

// Verificar token CSRF en peticiones POST y permisos de Administrador
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
// PROCESAMIENTO DE ACCIONES POST (Confirmar / Rechazar individual o masivo)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
  $action = $_POST['action'] ?? '';

  // 1. Confirmar una reserva puntual
  if ($action === 'confirm_single')
  {
    $entry_id = (int)($_POST['entry_id'] ?? 0);
    if ($entry_id > 0)
    {
      $mask = STATUS_AWAITING_APPROVAL | STATUS_TENTATIVE;
      $sql = "UPDATE " . _tbl('entry') . "
                 SET status = status & (~$mask),
                     ical_sequence = ical_sequence + 1
               WHERE id = ?";
      db()->command($sql, array($entry_id));
      $msg_success = "La reserva puntual #$entry_id ha sido confirmada exitosamente.";
    }
  }
  // 2. Confirmar una serie semestral completa
  elseif ($action === 'confirm_series')
  {
    $repeat_id = (int)($_POST['repeat_id'] ?? 0);
    if ($repeat_id > 0)
    {
      $mask = STATUS_AWAITING_APPROVAL | STATUS_TENTATIVE;
      $sql_rep = "UPDATE " . _tbl('repeat') . "
                     SET status = status & (~$mask),
                         ical_sequence = ical_sequence + 1
                   WHERE id = ?";
      db()->command($sql_rep, array($repeat_id));

      $sql_ent = "UPDATE " . _tbl('entry') . "
                     SET status = status & (~$mask),
                         ical_sequence = ical_sequence + 1
                   WHERE repeat_id = ? AND (status & $mask) != 0";
      $affected = db()->command($sql_ent, array($repeat_id));
      $msg_success = "La serie semestral #$repeat_id ($affected bloques) ha sido confirmada exitosamente.";
    }
  }
  // 3. Rechazar / Eliminar una reserva puntual
  elseif ($action === 'delete_single')
  {
    $entry_id = (int)($_POST['entry_id'] ?? 0);
    if ($entry_id > 0)
    {
      $sql = "DELETE FROM " . _tbl('entry') . " WHERE id = ?";
      db()->command($sql, array($entry_id));
      $msg_success = "La solicitud de reserva #$entry_id fue rechazada y eliminada del sistema.";
    }
  }
  // 4. Rechazar / Eliminar una serie semestral pendiente
  elseif ($action === 'delete_series')
  {
    $repeat_id = (int)($_POST['repeat_id'] ?? 0);
    if ($repeat_id > 0)
    {
      $mask = STATUS_AWAITING_APPROVAL | STATUS_TENTATIVE;
      $sql = "DELETE FROM " . _tbl('entry') . " WHERE repeat_id = ? AND (status & $mask) != 0";
      $deleted = db()->command($sql, array($repeat_id));

      // Si no quedan entradas asociadas en mrbs_entry, limpiar mrbs_repeat
      $rem = db()->query1("SELECT COUNT(*) FROM " . _tbl('entry') . " WHERE repeat_id = ?", array($repeat_id));
      if ($rem == 0)
      {
        db()->command("DELETE FROM " . _tbl('repeat') . " WHERE id = ?", array($repeat_id));
      }
      $msg_success = "La serie pendiente #$repeat_id ($deleted sesiones) fue rechazada y eliminada.";
    }
  }
  // 5. Confirmación Masiva
  elseif ($action === 'bulk_confirm')
  {
    $selected = $_POST['selected_items'] ?? array();
    if (is_array($selected) && !empty($selected))
    {
      $mask = STATUS_AWAITING_APPROVAL | STATUS_TENTATIVE;
      $count_singles = 0;
      $count_series  = 0;

      foreach ($selected as $item)
      {
        if (strpos($item, 'single_') === 0)
        {
          $id = (int)substr($item, 7);
          if ($id > 0)
          {
            db()->command("UPDATE " . _tbl('entry') . " SET status = status & (~$mask), ical_sequence = ical_sequence + 1 WHERE id = ?", array($id));
            $count_singles++;
          }
        }
        elseif (strpos($item, 'series_') === 0)
        {
          $rep_id = (int)substr($item, 7);
          if ($rep_id > 0)
          {
            db()->command("UPDATE " . _tbl('repeat') . " SET status = status & (~$mask), ical_sequence = ical_sequence + 1 WHERE id = ?", array($rep_id));
            db()->command("UPDATE " . _tbl('entry') . " SET status = status & (~$mask), ical_sequence = ical_sequence + 1 WHERE repeat_id = ? AND (status & $mask) != 0", array($rep_id));
            $count_series++;
          }
        }
      }
      $msg_success = "Confirmación masiva completada: $count_singles reservas puntuales y $count_series series semestrales confirmadas.";
    }
    else
    {
      $msg_error = "No seleccionó ninguna reserva para confirmar.";
    }
  }
  // 6. Eliminación / Rechazo Masivo
  elseif ($action === 'bulk_delete')
  {
    $selected = $_POST['selected_items'] ?? array();
    if (is_array($selected) && !empty($selected))
    {
      $mask = STATUS_AWAITING_APPROVAL | STATUS_TENTATIVE;
      $count_deleted = 0;

      foreach ($selected as $item)
      {
        if (strpos($item, 'single_') === 0)
        {
          $id = (int)substr($item, 7);
          if ($id > 0)
          {
            db()->command("DELETE FROM " . _tbl('entry') . " WHERE id = ?", array($id));
            $count_deleted++;
          }
        }
        elseif (strpos($item, 'series_') === 0)
        {
          $rep_id = (int)substr($item, 7);
          if ($rep_id > 0)
          {
            db()->command("DELETE FROM " . _tbl('entry') . " WHERE repeat_id = ? AND (status & $mask) != 0", array($rep_id));
            $rem = db()->query1("SELECT COUNT(*) FROM " . _tbl('entry') . " WHERE repeat_id = ?", array($rep_id));
            if ($rem == 0)
            {
              db()->command("DELETE FROM " . _tbl('repeat') . " WHERE id = ?", array($rep_id));
            }
            $count_deleted++;
          }
        }
      }
      $msg_success = "Se rechazaron y eliminaron $count_deleted solicitudes seleccionadas.";
    }
    else
    {
      $msg_error = "No seleccionó ninguna solicitud para rechazar.";
    }
  }
}

// =========================================================================
// PARÁMETROS DE FILTRADO (Por defecto Opción A: 'upcoming' = Desde hoy)
// =========================================================================
$filter_range = $_REQUEST['filter_range'] ?? 'upcoming'; // upcoming | week | month | all
$filter_kind  = $_REQUEST['filter_kind']  ?? 'all';      // all | single | series
$filter_area  = (int)($_REQUEST['filter_area'] ?? 0);
$filter_type  = trim((string)($_REQUEST['filter_type'] ?? ''));

$today_start = mktime(0, 0, 0);
$mask_pending = STATUS_AWAITING_APPROVAL | STATUS_TENTATIVE;

$where_clauses = array(
  "M.disabled = 0",
  "A.disabled = 0",
  "(E.status & $mask_pending) != 0"
);
$sql_params = array();

if ($filter_range === 'upcoming')
{
  $where_clauses[] = "E.start_time >= ?";
  $sql_params[] = $today_start;
}
elseif ($filter_range === 'week')
{
  $where_clauses[] = "E.start_time >= ? AND E.start_time <= ?";
  $sql_params[] = $today_start;
  $sql_params[] = $today_start + (7 * 86400);
}
elseif ($filter_range === 'month')
{
  $where_clauses[] = "E.start_time >= ? AND E.start_time <= ?";
  $sql_params[] = $today_start;
  $sql_params[] = $today_start + (30 * 86400);
}

if ($filter_area > 0)
{
  $where_clauses[] = "M.area_id = ?";
  $sql_params[] = $filter_area;
}

if ($filter_type !== '')
{
  $where_clauses[] = "E.type = ?";
  $sql_params[] = $filter_type;
}

$sql = "SELECT E.*, M.room_name, M.area_id, A.area_name, A.enable_periods
          FROM " . _tbl('entry') . " E
    INNER JOIN " . _tbl('room') . " M ON E.room_id = M.id
    INNER JOIN " . _tbl('area') . " A ON M.area_id = A.id
         WHERE " . implode(" AND ", $where_clauses) . "
      ORDER BY E.start_time ASC";

$res = db()->query($sql, $sql_params);

// Agrupar resultados en Reservas Puntuales y Series Semestrales
$single_bookings = array();
$series_bookings = array();

while (false !== ($row = $res->next_row_keyed()))
{
  if (!is_book_admin($row['room_id']))
  {
    continue;
  }

  $rep_id = !empty($row['repeat_id']) ? (int)$row['repeat_id'] : 0;

  if ($rep_id === 0)
  {
    $single_bookings[] = $row;
  }
  else
  {
    if (!isset($series_bookings[$rep_id]))
    {
      $series_bookings[$rep_id] = array(
        'repeat_id'       => $rep_id,
        'first_entry'     => $row,
        'count'           => 1,
        'min_start_time'  => (int)$row['start_time'],
        'max_start_time'  => (int)$row['start_time']
      );
    }
    else
    {
      $series_bookings[$rep_id]['count']++;
      if ((int)$row['start_time'] < $series_bookings[$rep_id]['min_start_time'])
      {
        $series_bookings[$rep_id]['min_start_time'] = (int)$row['start_time'];
      }
      if ((int)$row['start_time'] > $series_bookings[$rep_id]['max_start_time'])
      {
        $series_bookings[$rep_id]['max_start_time'] = (int)$row['start_time'];
      }
    }
  }
}

$total_singles = count($single_bookings);
$total_series  = count($series_bookings);
$total_items   = $total_singles + $total_series;

// Construir lista unificada para mostrar en la tabla según filtro de modalidad
$display_items = array();

if ($filter_kind === 'all' || $filter_kind === 'single')
{
  foreach ($single_bookings as $b)
  {
    $display_items[] = array(
      'item_kind'      => 'single',
      'key'            => 'single_' . $b['id'],
      'entry_id'       => (int)$b['id'],
      'repeat_id'      => 0,
      'count'          => 1,
      'sort_time'      => (int)$b['start_time'],
      'min_start_time' => (int)$b['start_time'],
      'max_start_time' => (int)$b['start_time'],
      'row'            => $b
    );
  }
}

if ($filter_kind === 'all' || $filter_kind === 'series')
{
  foreach ($series_bookings as $rep_id => $s_info)
  {
    $display_items[] = array(
      'item_kind'      => 'series',
      'key'            => 'series_' . $rep_id,
      'entry_id'       => (int)$s_info['first_entry']['id'],
      'repeat_id'      => (int)$rep_id,
      'count'          => (int)$s_info['count'],
      'sort_time'      => (int)$s_info['min_start_time'],
      'min_start_time' => (int)$s_info['min_start_time'],
      'max_start_time' => (int)$s_info['max_start_time'],
      'row'            => $s_info['first_entry']
    );
  }
}

// Ordenar cronológicamente por fecha más próxima
usort($display_items, function($a, $b) {
  return $a['sort_time'] <=> $b['sort_time'];
});

// Obtener lista de áreas para el selector de filtro
$areas_list = get_area_names();

// Renderizar cabecera institucional
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

<div class="udec-admin-types-wrapper udec-pending-wrapper">
  <div class="udec-admin-types-card">

    <!-- Cabecera del Panel -->
    <div class="udec-admin-header">
      <div class="udec-admin-header-title">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <h2>Gestión de Reservas Pendientes y Sin Confirmar</h2>
      </div>
      <span class="udec-admin-badge">Control de Solicitudes UdeC</span>
    </div>

    <div class="udec-admin-body">

      <!-- Alertas de Estado -->
      <?php if (!empty($msg_success)): ?>
        <div class="udec-alert udec-alert-success">
          <i class="fa-solid fa-circle-check"></i>
          <div><?php echo htmlspecialchars($msg_success); ?></div>
        </div>
      <?php endif; ?>

      <?php if (!empty($msg_error)): ?>
        <div class="udec-alert udec-alert-error">
          <i class="fa-solid fa-triangle-exclamation"></i>
          <div><?php echo htmlspecialchars($msg_error); ?></div>
        </div>
      <?php endif; ?>

      <!-- Tarjetas de Resumen Rápido (KPIs clicables) -->
      <div class="udec-pending-kpis">
        <a href="admin_pending.php?filter_range=<?php echo urlencode($filter_range); ?>&amp;filter_kind=all&amp;filter_area=<?php echo $filter_area; ?>&amp;filter_type=<?php echo urlencode($filter_type); ?>" 
           class="udec-kpi-card <?php echo ($filter_kind === 'all') ? 'active' : ''; ?>">
          <div class="udec-kpi-icon total"><i class="fa-solid fa-layer-group"></i></div>
          <div class="udec-kpi-info">
            <span class="udec-kpi-num"><?php echo $total_items; ?></span>
            <span class="udec-kpi-label">Total Solicitudes Pendientes</span>
          </div>
        </a>

        <a href="admin_pending.php?filter_range=<?php echo urlencode($filter_range); ?>&amp;filter_kind=single&amp;filter_area=<?php echo $filter_area; ?>&amp;filter_type=<?php echo urlencode($filter_type); ?>" 
           class="udec-kpi-card <?php echo ($filter_kind === 'single') ? 'active' : ''; ?>">
          <div class="udec-kpi-icon single"><i class="fa-solid fa-calendar-day"></i></div>
          <div class="udec-kpi-info">
            <span class="udec-kpi-num"><?php echo $total_singles; ?></span>
            <span class="udec-kpi-label">Reservas Puntuales / Únicas</span>
          </div>
        </a>

        <a href="admin_pending.php?filter_range=<?php echo urlencode($filter_range); ?>&amp;filter_kind=series&amp;filter_area=<?php echo $filter_area; ?>&amp;filter_type=<?php echo urlencode($filter_type); ?>" 
           class="udec-kpi-card <?php echo ($filter_kind === 'series') ? 'active' : ''; ?>">
          <div class="udec-kpi-icon series"><i class="fa-solid fa-repeat"></i></div>
          <div class="udec-kpi-info">
            <span class="udec-kpi-num"><?php echo $total_series; ?></span>
            <span class="udec-kpi-label">Series Semestrales Agrupadas</span>
          </div>
        </a>
      </div>

      <!-- Barra de Filtros -->
      <form method="get" action="admin_pending.php" class="udec-pending-filters">
        <div class="udec-filter-group">
          <label for="filter_range"><i class="fa-regular fa-calendar"></i> Horizonte:</label>
          <select name="filter_range" id="filter_range" onchange="this.form.submit()">
            <option value="upcoming" <?php echo ($filter_range === 'upcoming') ? 'selected' : ''; ?>>Desde hoy en adelante</option>
            <option value="week" <?php echo ($filter_range === 'week') ? 'selected' : ''; ?>>Próximos 7 días</option>
            <option value="month" <?php echo ($filter_range === 'month') ? 'selected' : ''; ?>>Próximos 30 días</option>
            <option value="all" <?php echo ($filter_range === 'all') ? 'selected' : ''; ?>>Todas (incluyendo pasadas)</option>
          </select>
        </div>

        <div class="udec-filter-group">
          <label for="filter_kind"><i class="fa-solid fa-sliders"></i> Modalidad:</label>
          <select name="filter_kind" id="filter_kind" onchange="this.form.submit()">
            <option value="all" <?php echo ($filter_kind === 'all') ? 'selected' : ''; ?>>Todas (Puntuales + Series)</option>
            <option value="single" <?php echo ($filter_kind === 'single') ? 'selected' : ''; ?>>Solo Reservas Puntuales</option>
            <option value="series" <?php echo ($filter_kind === 'series') ? 'selected' : ''; ?>>Solo Series Semestrales</option>
          </select>
        </div>

        <div class="udec-filter-group">
          <label for="filter_area"><i class="fa-regular fa-building"></i> Edificio:</label>
          <select name="filter_area" id="filter_area" onchange="this.form.submit()">
            <option value="0">Todos los edificios</option>
            <?php foreach ($areas_list as $a_id => $a_name): ?>
              <option value="<?php echo (int)$a_id; ?>" <?php echo ($filter_area === (int)$a_id) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($a_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="udec-filter-group udec-filter-search">
          <label for="pendingSearchInput"><i class="fa-solid fa-magnifying-glass"></i> Buscar:</label>
          <input type="text" id="pendingSearchInput" placeholder="Filtrar por asignatura, docente, sala...">
        </div>
      </form>

      <?php if (empty($display_items)): ?>
        <div class="udec-pending-empty">
          <i class="fa-regular fa-circle-check"></i>
          <h3>¡Todo al día! No hay reservas pendientes para los filtros seleccionados</h3>
          <p>Puede cambiar el horizonte de fechas o modalidad en la barra superior para consultar otros registros.</p>
        </div>
      <?php else: ?>

        <!-- Formulario para Acciones Individuales y Masivas -->
        <form method="post" action="admin_pending.php" id="formPendingActions">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(Form::getToken()); ?>">
          <input type="hidden" name="filter_range" value="<?php echo htmlspecialchars($filter_range); ?>">
          <input type="hidden" name="filter_kind" value="<?php echo htmlspecialchars($filter_kind); ?>">
          <input type="hidden" name="filter_area" value="<?php echo (int)$filter_area; ?>">
          <input type="hidden" name="filter_type" value="<?php echo htmlspecialchars($filter_type); ?>">

          <!-- Barra de Acciones en Lote -->
          <div class="udec-bulk-bar">
            <div class="udec-bulk-left">
              <label class="udec-checkbox-label">
                <input type="checkbox" id="checkAllPending">
                <span>Seleccionar todas las visibles</span>
              </label>
              <span id="selectedCountBadge" class="udec-selected-count" style="display:none;">0 seleccionadas</span>
            </div>
            <div class="udec-bulk-right">
              <button type="submit" name="action" value="bulk_confirm" class="udec-btn udec-btn-confirm-bulk" onclick="return confirm('¿Confirmar y aprobar todas las reservas seleccionadas?');">
                <i class="fa-solid fa-check-double"></i> Confirmar Seleccionadas
              </button>
              <button type="submit" name="action" value="bulk_delete" class="udec-btn udec-btn-delete-bulk" onclick="return confirm('¿Está seguro de rechazar y eliminar las reservas seleccionadas? Esta acción no se puede deshacer.');">
                <i class="fa-regular fa-trash-can"></i> Rechazar Seleccionadas
              </button>
            </div>
          </div>

          <div class="udec-table-container">
            <table class="udec-admin-table udec-pending-table" id="tablePending">
              <thead>
                <tr>
                  <th style="width: 38px; text-align: center;"></th>
                  <th style="width: 135px;">Modalidad / Estado</th>
                  <th>Actividad / Asignatura &amp; Solicitante</th>
                  <th style="width: 190px;">Edificio / Recinto</th>
                  <th style="width: 210px;">Fecha y Bloque Horario</th>
                  <th style="width: 230px; text-align: center;">Acciones Rápidas</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($display_items as $item): ?>
                  <?php
                    $b = $item['row'];
                    $is_series = ($item['item_kind'] === 'series');
                    $entry_id  = $item['entry_id'];
                    $repeat_id = $item['repeat_id'];
                    $type_code = $b['type'] ?? 'I';
                    $type_name = get_type_vocab($type_code);
                    $solicitante = !empty($b['solicitante']) ? $b['solicitante'] : ($b['create_by'] ?? '');
                    $time_slot = get_hover_time_slot_string((int)$b['start_time'], (int)$b['end_time'], (int)$b['room_id'], (int)$b['area_id']);

                    $is_awaiting_approval = (((int)$b['status'] & STATUS_AWAITING_APPROVAL) !== 0);
                    $status_label = $is_awaiting_approval ? 'Por Aprobar' : 'Sin Confirmar';

                    // Fechas formateadas
                    $d_start = date('d/m/Y', $item['min_start_time']);
                    $d_end   = date('d/m/Y', $item['max_start_time']);
                    $cal_year  = (int)date('Y', $item['min_start_time']);
                    $cal_month = (int)date('m', $item['min_start_time']);
                    $cal_day   = (int)date('d', $item['min_start_time']);

                    $cal_url = "index.php?view=week&year=$cal_year&month=$cal_month&day=$cal_day&area=" . (int)$b['area_id'] . "&room=" . (int)$b['room_id'];
                    $view_url = $is_series ? "view_entry.php?id=$entry_id" : "view_entry.php?id=$entry_id";
                  ?>
                  <tr class="pending-row">
                    <!-- Checkbox -->
                    <td style="text-align: center;">
                      <input type="checkbox" name="selected_items[]" value="<?php echo htmlspecialchars($item['key']); ?>" class="pending-row-check">
                    </td>

                    <!-- Modalidad y Estado -->
                    <td>
                      <?php if ($is_series): ?>
                        <span class="udec-kind-badge series" title="Serie repetitiva semestral">
                          <i class="fa-solid fa-repeat"></i> Serie (<?php echo $item['count']; ?> ses.)
                        </span>
                      <?php else: ?>
                        <span class="udec-kind-badge single" title="Reserva única puntual">
                          <i class="fa-solid fa-calendar-day"></i> Puntual
                        </span>
                      <?php endif; ?>
                      <div style="margin-top: 5px;">
                        <span class="udec-status-pill">
                          <i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($status_label); ?>
                        </span>
                      </div>
                    </td>

                    <!-- Actividad, Carrera y Solicitante -->
                    <td>
                      <div class="udec-pending-title">
                        <a href="<?php echo htmlspecialchars($view_url); ?>">
                          <?php echo htmlspecialchars($b['name']); ?>
                        </a>
                      </div>
                      <div class="udec-pending-meta">
                        <span class="udec-hc-badge <?php echo htmlspecialchars($type_code); ?>" style="font-size: 9.5px; padding: 2px 7px;">
                          <?php echo htmlspecialchars($type_name); ?>
                        </span>
                        <span class="udec-pending-user">
                          <i class="fa-regular fa-user"></i> <strong>Solicitante:</strong> <?php echo htmlspecialchars($solicitante); ?>
                        </span>
                      </div>
                      <?php if (!empty($b['description'])): ?>
                        <div class="udec-pending-desc">
                          <?php echo htmlspecialchars($b['description']); ?>
                        </div>
                      <?php endif; ?>
                    </td>

                    <!-- Edificio y Sala -->
                    <td>
                      <div class="udec-pending-room"><?php echo htmlspecialchars($b['room_name']); ?></div>
                      <div class="udec-pending-area"><?php echo htmlspecialchars($b['area_name']); ?></div>
                    </td>

                    <!-- Fecha y Bloque Horario -->
                    <td>
                      <?php if ($is_series): ?>
                        <div class="udec-pending-date">
                          <i class="fa-regular fa-calendar-days"></i> <?php echo $d_start; ?> al <?php echo $d_end; ?>
                        </div>
                      <?php else: ?>
                        <div class="udec-pending-date">
                          <i class="fa-regular fa-calendar"></i> <?php echo $d_start; ?>
                        </div>
                      <?php endif; ?>
                      <div class="udec-pending-time">
                        <i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($time_slot); ?> hrs
                      </div>
                    </td>

                    <!-- Acciones Rápidas -->
                    <td style="text-align: center; white-space: nowrap;">
                      <div class="udec-pending-actions">
                        <?php if ($is_series): ?>
                          <button type="button" 
                                  class="udec-action-btn confirm" 
                                  title="Confirmar todas las sesiones de esta serie"
                                  onclick="submitSingleAction('confirm_series', 0, <?php echo $repeat_id; ?>)">
                            <i class="fa-solid fa-check"></i> Confirmar
                          </button>
                        <?php else: ?>
                          <button type="button" 
                                  class="udec-action-btn confirm" 
                                  title="Confirmar esta reserva"
                                  onclick="submitSingleAction('confirm_single', <?php echo $entry_id; ?>, 0)">
                            <i class="fa-solid fa-check"></i> Confirmar
                          </button>
                        <?php endif; ?>

                        <a href="<?php echo htmlspecialchars($cal_url); ?>" 
                           class="udec-action-btn calendar" 
                           title="Ver ubicada en la grilla de horarios">
                          <i class="fa-regular fa-calendar"></i>
                        </a>

                        <a href="<?php echo htmlspecialchars($view_url); ?>" 
                           class="udec-action-btn detail" 
                           title="Ver o editar ficha completa">
                          <i class="fa-regular fa-eye"></i>
                        </a>

                        <?php if ($is_series): ?>
                          <button type="button" 
                                  class="udec-action-btn reject" 
                                  title="Rechazar y eliminar toda esta serie pendiente"
                                  onclick="if(confirm('¿Rechazar y eliminar las <?php echo $item['count']; ?> sesiones de esta serie?')) submitSingleAction('delete_series', <?php echo $entry_id; ?>, <?php echo $repeat_id; ?>)">
                            <i class="fa-regular fa-trash-can"></i>
                          </button>
                        <?php else: ?>
                          <button type="button" 
                                  class="udec-action-btn reject" 
                                  title="Rechazar y eliminar esta solicitud"
                                  onclick="if(confirm('¿Rechazar y eliminar esta reserva pendiente?')) submitSingleAction('delete_single', <?php echo $entry_id; ?>, 0)">
                            <i class="fa-regular fa-trash-can"></i>
                          </button>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </form>

        <!-- Formulario oculto auxiliar para botones de 1 clic por fila -->
        <form method="post" action="admin_pending.php" id="formSingleRowAction" style="display:none;">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(Form::getToken()); ?>">
          <input type="hidden" name="action" id="singleRowActionName" value="">
          <input type="hidden" name="entry_id" id="singleRowEntryId" value="0">
          <input type="hidden" name="repeat_id" id="singleRowRepeatId" value="0">
          <input type="hidden" name="filter_range" value="<?php echo htmlspecialchars($filter_range); ?>">
          <input type="hidden" name="filter_kind" value="<?php echo htmlspecialchars($filter_kind); ?>">
          <input type="hidden" name="filter_area" value="<?php echo (int)$filter_area; ?>">
          <input type="hidden" name="filter_type" value="<?php echo htmlspecialchars($filter_type); ?>">
        </form>

      <?php endif; ?>

    </div>
  </div>
</div>

<script>
function submitSingleAction(actionName, entryId, repeatId) {
  document.getElementById('singleRowActionName').value = actionName;
  document.getElementById('singleRowEntryId').value = entryId;
  document.getElementById('singleRowRepeatId').value = repeatId;
  document.getElementById('formSingleRowAction').submit();
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('pendingSearchInput');
  const checkAll = document.getElementById('checkAllPending');
  const rowChecks = document.querySelectorAll('.pending-row-check');
  const countBadge = document.getElementById('selectedCountBadge');

  function updateSelectedCount() {
    if (!countBadge) return;
    const checked = document.querySelectorAll('.pending-row-check:checked').length;
    if (checked > 0) {
      countBadge.textContent = checked + (checked === 1 ? ' seleccionada' : ' seleccionadas');
      countBadge.style.display = 'inline-block';
    } else {
      countBadge.style.display = 'none';
    }
  }

  if (checkAll) {
    checkAll.addEventListener('change', function() {
      const visibleChecks = Array.from(rowChecks).filter(ch => ch.closest('tr').style.display !== 'none');
      visibleChecks.forEach(ch => { ch.checked = checkAll.checked; });
      updateSelectedCount();
    });
  }

  rowChecks.forEach(ch => {
    ch.addEventListener('change', updateSelectedCount);
  });

  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const term = searchInput.value.toLowerCase().trim();
      document.querySelectorAll('#tablePending tbody tr.pending-row').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = (term === '' || text.indexOf(term) !== -1) ? '' : 'none';
      });
    });
  }
});
</script>

<?php
print_footer();
?>
