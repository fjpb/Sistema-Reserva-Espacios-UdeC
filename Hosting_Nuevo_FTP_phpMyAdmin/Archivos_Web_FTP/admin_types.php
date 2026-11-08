<?php
declare(strict_types=1);
namespace MRBS;

use MRBS\Form\Form;

require "defaultincludes.inc";

// Verificar autenticación y permisos de administrador (Nivel >= 2)
Form::checkToken(true);
checkAuthorised(this_page());

if (!is_admin())
{
  showAccessDenied();
  exit;
}

$types_file = __DIR__ . '/Themes/udec/types_config.json';

// Valores por defecto institucionales de la Universidad de Concepción
$default_types = array(
  array("code" => "E", "name" => "Externa", "color" => "#CBD5E1", "enabled" => true),
  array("code" => "I", "name" => "Interna", "color" => "#6EE7B7", "enabled" => true),
  array("code" => "A", "name" => "General", "color" => "#92D050", "enabled" => true),
  array("code" => "B", "name" => "Auditoría", "color" => "#9999FF", "enabled" => true),
  array("code" => "C", "name" => "Ingeniería Comercial", "color" => "#F1A983", "enabled" => true),
  array("code" => "D", "name" => "Ingeniería Geomática", "color" => "#94DCF8", "enabled" => true),
  array("code" => "F", "name" => "Ingeniería en Biotecnología Vegetal", "color" => "#A9D08E", "enabled" => true),
  array("code" => "G", "name" => "Pedagogía en Ciencias Naturales y Biología (PCNB)", "color" => "#FEF08A", "enabled" => true),
  array("code" => "H", "name" => "Pedagogía en Matemáticas", "color" => "#F2CEEF", "enabled" => true),
  array("code" => "J", "name" => "Pedagogía en Inglés", "color" => "#6FF99D", "enabled" => true),
  array("code" => "K", "name" => "Pedagogía en Español", "color" => "#FFC000", "enabled" => true),
  array("code" => "L", "name" => "Educación General Básica", "color" => "#6DE3FB", "enabled" => true),
  array("code" => "M", "name" => "Educación Diferencial", "color" => "#F472B6", "enabled" => true),
  array("code" => "N", "name" => "Enfermería", "color" => "#FACC15", "enabled" => true),
  array("code" => "O", "name" => "CADE", "color" => "#C084FC", "enabled" => true),
  array("code" => "P", "name" => "Escuela Educación", "color" => "#FBBF24", "enabled" => true),
  array("code" => "Q", "name" => "Escuela Ciencias", "color" => "#60A5FA", "enabled" => true),
  array("code" => "R", "name" => "Dirección General", "color" => "#86EFAC", "enabled" => true),
  array("code" => "S", "name" => "Unidad Vinculación con el Medio", "color" => "#2DD4BF", "enabled" => true),
  array("code" => "T", "name" => "Subdirección Académica", "color" => "#A3E635", "enabled" => true),
  array("code" => "U", "name" => "Oficina Extensión AyC", "color" => "#FB7185", "enabled" => true),
  array("code" => "V", "name" => "Unidad de Deportes", "color" => "#38BDF8", "enabled" => true),
  array("code" => "X", "name" => "Unidad Administración", "color" => "#4ADE80", "enabled" => true),
  array("code" => "Y", "name" => "Unidad Investigación e Innovación", "color" => "#EAB308", "enabled" => true)
);

$msg_success = '';
$msg_error = '';

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
  $action = $_POST['action'] ?? '';

  if ($action === 'reset')
  {
    if (file_put_contents($types_file, json_encode($default_types, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false)
    {
      $msg_success = 'Se han restablecido exitosamente los 24 tipos de reserva y colores institucionales originales.';
    }
    else
    {
      $msg_error = 'Error: No se pudo escribir en el archivo de configuración. Verifique los permisos del servidor.';
    }
  }
  elseif ($action === 'save')
  {
    $raw_types = $_POST['types'] ?? array();
    $clean_types = array();
    $seen_codes = array();
    $has_duplicate = false;

    if (is_array($raw_types))
    {
      foreach ($raw_types as $item)
      {
        $code = strtoupper(trim((string)($item['code'] ?? '')));
        $name = trim((string)($item['name'] ?? ''));
        $color = trim((string)($item['color'] ?? '#92D050'));
        $enabled = !empty($item['enabled']);

        // Validación básica
        if ($code === '' || $name === '')
        {
          continue;
        }

        // Sanitizar código (solo letras, números y guión bajo)
        $code = preg_replace('/[^A-Z0-9_]/', '', $code);
        if ($code === '')
        {
          continue;
        }

        // Verificar unicidad de código
        if (isset($seen_codes[$code]))
        {
          $has_duplicate = true;
          $msg_error = "El código '$code' está duplicado. Cada tipo de reserva debe tener un código único.";
          break;
        }
        $seen_codes[$code] = true;

        // Validar color hexadecimal
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color))
        {
          $color = '#92D050';
        }

        $clean_types[] = array(
          'code'    => $code,
          'name'    => $name,
          'color'   => strtoupper($color),
          'enabled' => $enabled
        );
      }
    }

    if (!$has_duplicate)
    {
      if (empty($clean_types))
      {
        $msg_error = 'Debe registrar al menos un tipo de reserva activo.';
      }
      else
      {
        if (file_put_contents($types_file, json_encode($clean_types, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false)
        {
          $msg_success = '¡Cambios guardados con éxito! Los tipos de reserva y colores institucionales han sido actualizados.';
        }
        else
        {
          $msg_error = 'Error al guardar la configuración. Verifique permisos de escritura en ' . htmlspecialchars($types_file);
        }
      }
    }
  }
}

// Cargar configuración actual
$current_types = $default_types;
if (is_file($types_file))
{
  $loaded = json_decode(file_get_contents($types_file), true);
  if (is_array($loaded) && !empty($loaded))
  {
    $current_types = $loaded;
  }
}

// Renderizar cabecera estándar MRBS con contexto UdeC
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

<div class="udec-admin-types-wrapper">
  <div class="udec-admin-types-card">
    
    <!-- Encabezado del Módulo -->
    <div class="udec-admin-header">
      <div class="udec-admin-header-title">
        <i class="fa-solid fa-palette"></i>
        <h2>Gestión Institucional de Tipos y Colores de Reserva</h2>
      </div>
      <span class="udec-admin-badge">Panel Administrativo UdeC</span>
    </div>

    <div class="udec-admin-body">
      
      <!-- Mensajes de Estado -->
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

      <!-- Guía Explicativa -->
      <div class="udec-admin-instructions">
        <i class="fa-solid fa-circle-info"></i>
        <div>
          <strong>Configuración Visual de Carreras, Solicitudes y Colores:</strong>
          <p>
            Modifique los nombres de las carreras/unidades y seleccione sus colores oficiales con el selector interactivo. 
            Los cambios se reflejarán inmediatamente en la <strong>grilla de horarios</strong>, las <strong>tarjetas flotantes (hover cards)</strong>, 
            el <strong>formulario de reservas</strong> y el <strong>filtro interactivo</strong> de la parte inferior.
          </p>
        </div>
      </div>

      <!-- Formulario Principal -->
      <form method="post" action="admin_types.php" id="formAdminTypes">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(Form::getToken()); ?>">
        
        <div class="udec-table-container">
          <table class="udec-admin-table" id="tableTypes">
            <thead>
              <tr>
                <th style="width: 80px; text-align: center;">Código</th>
                <th>Carrera / Tipo de Reserva / Unidad</th>
                <th style="width: 170px;">Color Institucional</th>
                <th style="width: 220px; text-align: center;">Vista Previa en Vivo</th>
                <th style="width: 90px; text-align: center;">Activo</th>
                <th style="width: 80px; text-align: center;">Acción</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($current_types as $index => $t): ?>
                <?php
                  $code = htmlspecialchars($t['code'] ?? '');
                  $name = htmlspecialchars($t['name'] ?? '');
                  $color = htmlspecialchars($t['color'] ?? '#92D050');
                  $enabled = !empty($t['enabled']);
                ?>
                <tr class="type-row" data-row-id="<?php echo $index; ?>">
                  <!-- Código -->
                  <td style="text-align: center;">
                    <input type="text" 
                           name="types[<?php echo $index; ?>][code]" 
                           value="<?php echo $code; ?>" 
                           maxlength="4" 
                           class="type-code-input" 
                           required 
                           style="text-transform: uppercase;">
                  </td>

                  <!-- Nombre / Carrera -->
                  <td>
                    <input type="text" 
                           name="types[<?php echo $index; ?>][name]" 
                           value="<?php echo $name; ?>" 
                           class="type-name-input" 
                           placeholder="Ej: Auditoría, Ped. Matemáticas..." 
                           required>
                  </td>

                  <!-- Color Picker y Hex -->
                  <td>
                    <div class="color-picker-wrap">
                      <input type="color" 
                             value="<?php echo $color; ?>" 
                             class="type-color-picker" 
                             title="Haz clic para elegir un color">
                      <input type="text" 
                             name="types[<?php echo $index; ?>][color]" 
                             value="<?php echo $color; ?>" 
                             maxlength="7" 
                             class="type-color-hex" 
                             pattern="^#[0-9a-fA-F]{6}$" 
                             required>
                    </div>
                  </td>

                  <!-- Vista Previa en Vivo -->
                  <td style="text-align: center;">
                    <span class="type-preview-badge" style="background-color: <?php echo $color; ?>;">
                      <?php echo $name ?: 'Ejemplo'; ?>
                    </span>
                  </td>

                  <!-- Estado Activo -->
                  <td style="text-align: center;">
                    <label class="udec-switch" title="Habilitar/Deshabilitar este tipo en el sistema">
                      <input type="checkbox" 
                             name="types[<?php echo $index; ?>][enabled]" 
                             value="1" 
                             <?php echo $enabled ? 'checked' : ''; ?>>
                      <span class="udec-slider"></span>
                    </label>
                  </td>

                  <!-- Acción Eliminar -->
                  <td style="text-align: center;">
                    <button type="button" class="btn-remove-row" title="Eliminar este tipo">
                      <i class="fa-regular fa-trash-can"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Botón para añadir nueva fila -->
        <div class="udec-add-row-bar">
          <button type="button" id="btnAddTypeRow" class="udec-btn udec-btn-add">
            <i class="fa-solid fa-plus"></i> Agregar Nuevo Tipo o Carrera
          </button>
        </div>

        <!-- Barra Inferior de Acciones -->
        <div class="udec-actions-bar">
          <div class="udec-actions-left">
            <button type="submit" name="action" value="save" class="udec-btn udec-btn-primary">
              <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
            </button>
            <a href="index.php" class="udec-btn udec-btn-outline">
              <i class="fa-solid fa-arrow-left"></i> Volver al Calendario
            </a>
          </div>
          <div class="udec-actions-right">
            <button type="submit" 
                    name="action" 
                    value="reset" 
                    class="udec-btn udec-btn-secondary" 
                    onclick="return confirm('¿Está seguro de que desea restablecer todos los tipos y colores originales de la Universidad de Concepción?');">
              <i class="fa-solid fa-rotate-left"></i> Restablecer Valores por Defecto
            </button>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const tableBody = document.querySelector('#tableTypes tbody');
  const btnAdd = document.getElementById('btnAddTypeRow');

  // Función para calcular contraste de texto (oscuro o blanco)
  function getContrastYIQ(hexcolor){
    hexcolor = hexcolor.replace('#', '');
    if(hexcolor.length === 3) {
      hexcolor = hexcolor.split('').map(c => c + c).join('');
    }
    const r = parseInt(hexcolor.substr(0,2),16);
    const g = parseInt(hexcolor.substr(2,2),16);
    const b = parseInt(hexcolor.substr(4,2),16);
    const yiq = ((r*299)+(g*587)+(b*114))/1000;
    return (yiq >= 150) ? '#1E293B' : '#FFFFFF';
  }

  // Vincular eventos de una fila
  function bindRowEvents(row) {
    const colorPicker = row.querySelector('.type-color-picker');
    const colorHex = row.querySelector('.type-color-hex');
    const nameInput = row.querySelector('.type-name-input');
    const previewBadge = row.querySelector('.type-preview-badge');
    const btnRemove = row.querySelector('.btn-remove-row');

    function updatePreview() {
      const color = colorHex.value.trim() || '#92D050';
      const name = nameInput.value.trim() || 'Ejemplo';
      previewBadge.style.backgroundColor = color;
      previewBadge.style.color = getContrastYIQ(color);
      previewBadge.textContent = name;
    }

    if (colorPicker && colorHex) {
      colorPicker.addEventListener('input', function() {
        colorHex.value = colorPicker.value.toUpperCase();
        updatePreview();
      });

      colorHex.addEventListener('input', function() {
        let val = colorHex.value.trim();
        if (!val.startsWith('#')) {
          val = '#' + val;
        }
        if (/^#[0-9a-fA-F]{6}$/.test(val)) {
          colorPicker.value = val;
          updatePreview();
        }
      });
    }

    if (nameInput) {
      nameInput.addEventListener('input', updatePreview);
    }

    if (btnRemove) {
      btnRemove.addEventListener('click', function() {
        if (document.querySelectorAll('#tableTypes tbody tr').length <= 1) {
          alert('Debe mantener al menos un tipo de reserva.');
          return;
        }
        if (confirm('¿Eliminar esta fila de tipos?')) {
          row.remove();
        }
      });
    }

    // Inicializar contraste en carga inicial
    if (colorHex) {
      updatePreview();
    }
  }

  // Vincular todas las filas existentes
  document.querySelectorAll('#tableTypes tbody tr').forEach(bindRowEvents);

  // Agregar nueva fila
  if (btnAdd) {
    btnAdd.addEventListener('click', function() {
      const rowCount = document.querySelectorAll('#tableTypes tbody tr').length;
      const nextIndex = Date.now(); // ID único para el índice de inputs
      
      // Proponer código disponible (A-Z)
      const existingCodes = Array.from(document.querySelectorAll('.type-code-input')).map(input => input.value.toUpperCase().trim());
      let candidate = '';
      for (let i = 65; i <= 90; i++) {
        const char = String.fromCharCode(i);
        if (!existingCodes.includes(char)) {
          candidate = char;
          break;
        }
      }
      if (!candidate) candidate = 'Z' + (rowCount + 1);

      const defaultColor = '#38BDF8';

      const tr = document.createElement('tr');
      tr.className = 'type-row';
      tr.dataset.rowId = nextIndex;
      tr.innerHTML = `
        <td style="text-align: center;">
          <input type="text" name="types[${nextIndex}][code]" value="${candidate}" maxlength="4" class="type-code-input" required style="text-transform: uppercase;">
        </td>
        <td>
          <input type="text" name="types[${nextIndex}][name]" value="Nueva Carrera / Tipo" class="type-name-input" required>
        </td>
        <td>
          <div class="color-picker-wrap">
            <input type="color" value="${defaultColor}" class="type-color-picker">
            <input type="text" name="types[${nextIndex}][color]" value="${defaultColor}" maxlength="7" class="type-color-hex" pattern="^#[0-9a-fA-F]{6}$" required>
          </div>
        </td>
        <td style="text-align: center;">
          <span class="type-preview-badge" style="background-color: ${defaultColor}; color: #1E293B;">Nueva Carrera / Tipo</span>
        </td>
        <td style="text-align: center;">
          <label class="udec-switch">
            <input type="checkbox" name="types[${nextIndex}][enabled]" value="1" checked>
            <span class="udec-slider"></span>
          </label>
        </td>
        <td style="text-align: center;">
          <button type="button" class="btn-remove-row" title="Eliminar este tipo">
            <i class="fa-regular fa-trash-can"></i>
          </button>
        </td>
      `;

      tableBody.appendChild(tr);
      bindRowEvents(tr);
      tr.querySelector('.type-name-input').focus();
      tr.querySelector('.type-name-input').select();
    });
  }
});
</script>

<?php
print_footer();
?>
