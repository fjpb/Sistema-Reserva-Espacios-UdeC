# 📜 Historial de Cambios y Registro de Actividades (`history.md`)
## Sistema de Gestión Horaria y Reserva de Espacios Académicos

Este documento registra cronológicamente todas las acciones técnicas, configuraciones, refactorizaciones y despliegues realizados en el proyecto.

---

### [2026-11-06] - Fase 1: Contenerización en Docker y Configuración de Entorno Local

#### 1. Diagnóstico Inicial del Repositorio Legacy
- **Análisis de estructura:** Se identificó la carpeta de código heredado con la aplicación **MRBS 1.11.6** y el volcado de base de datos MySQL/MariaDB exportado desde phpMyAdmin (`mbrs_0611unidadti.sql`, ~8.6 MB).
- **Inspección de base de datos:** El volcado contenía 28.572 reservas históricas (`mrbs_entry`), 53 recintos (`mrbs_room`), 14 áreas/facultades (`mrbs_area`) y 13 usuarios institucionales con contraseñas cifradas en bcrypt (`mrbs_users`).
- **Definiciones horarias institucionales:** Se verificó la parametrización de bloques de clase del Campus Los Ángeles (13 módulos desde las 08:15 hasta las 18:30 hrs) y los códigos de carrera asociados (A a Y).

#### 2. Implementación de Infraestructura Docker
- **Creación de `Dockerfile` para servicio Web:**
  - Base: `php:8.2-apache`.
  - Instalación de librerías del sistema: `libicu-dev`, `libpng-dev`, `libjpeg-dev`, `libfreetype6-dev`, `libzip-dev`, `locales`.
  - Compilación de extensiones PHP: `intl`, `pdo_mysql`, `mysqli`, `gd`, `zip`, `opcache`.
  - Habilitación de `mod_rewrite` en Apache.
  - Generación de locales POSIX: `es_CL.UTF-8`, `es_ES.UTF-8`, `en_US.UTF-8`.
  - Ajustes de rendimiento en `mrbs-custom.ini`: `date.timezone = America/Santiago`, `memory_limit = 256M`, `output_buffering = 4096`, `upload_max_filesize = 64M`.
- **Creación de orquestador `docker-compose.yml`:**
  - Servicio **`db`** (`mariadb:10.11`): inicialización automática montando el volcado SQL en `/docker-entrypoint-initdb.d/`, puerto host expuesto `3307:3306`, volumen persistente.
  - Servicio **`web`**: puerto host `8085:80`, montaje bind mount de `./mrbs` en `/var/www/html` para permitir **Hot Reloading / desarrollo en vivo**.
  - Servicio **`phpmyadmin`**: puerto host `8086:80` para administración gráfica directa de la base de datos `unidadti`.

#### 3. Refactorización de Nomenclatura a `sistemadereserva`
- Renombrado de la carpeta principal de `Sistema Actual 2026` a `sistemadereserva`.
- Actualización de los nombres de servicios, contenedores y volúmenes Docker a:
  - `sistemadereserva_web`
  - `sistemadereserva_db`
  - `sistemadereserva_phpmyadmin`
  - Volumen: `sistemadereserva_db_data`
- Parametrización en `config.inc.php` para admitir variables de entorno (`DB_HOST`, `DB_DATABASE`, `DB_LOGIN`, `DB_PASSWORD`, `DB_PORT`) con fallback retrocompatible.

#### 4. Detección y Resolución de Incidencia de Carga en Navegadores Safari
- **Incidencia:** Safari enviaba cabecera `Accept-Language: es-419` provocando advertencia `Server failed to set locale...`, lo cual enviaba salida al navegador antes de `Session::init()` y rompía la carga de estilos y scripts con `ini_set(): Session ini settings cannot be changed after headers have already been sent`.
- **Corrección:**
  - Fijación obligatoria de `$override_locale = 'es-CL';` y `$default_language_tokens = 'es';` en `config.inc.php`.
  - Activación de `output_buffering = 4096` y `display_errors = Off` en PHP para proteger las cabeceras HTTP de sesión.
- **Resultado:** Interfaz 100% funcional y renderizada en español en [http://localhost:8085](http://localhost:8085).

---

### [2026-11-06] - Fase 2: Implementación del Tema Institucional UdeC (`Themes/udec`)
- **Objetivo:** Modernizar la interfaz gráfica de MRBS integrando el diseño institucional de la Universidad de Concepción (`Demo nuevo html/sistema_de_reservas_udec_tema_institucional_mrbs.html`) con cero alteración de la Base de Datos.
- **Acciones realizadas:**
  1. **Creación del paquete de tema modular `Themes/udec`:**
     - `Themes/udec/styling.inc`: Paleta institucional oficial UdeC (Azul Marino `#002B49`, Dorado `#E89D1E`, Azul DTI `#003865`, Fondo Ice `#F8FAFC`, paleta de carreras A-Y).
     - `Themes/udec/header.inc`: Cabecera moderna con el Escudo UdeC oficial, títulos jerárquicos ("Universidad de Concepción", "Portal de Reservas - Campus Los Ángeles"), insignia de "Período Académico Activo", accesos a Reporte/Búsqueda y gestión de sesión.
     - `Themes/udec/custom.css`: Inyección de Google Font **Inter**, **Font Awesome 6**, grilla con bordes suaves (`#E2E8F0`), esquinas redondeadas (`border-radius: 12px`), tarjetas elevadas con sombra sutil (`box-shadow`), badges estilizados para las carreras y formularios modernizados.
  2. **Limpieza de CSS base:**
     - Eliminación de reglas hardcodeadas con `!important` que generaban bordes negros toscos en la grilla y fuentes desalineadas en `mrbs.css.php`.
  3. **Activación no destructiva en `config.inc.php`:**
     - `$theme = "udec";`
     - `$custom_css_url = "Themes/udec/custom.css";`
  4. **Ajuste de Pantalla Completa y Corrección de Carreras:**
     - Ocultación nativa del mini-calendario lateral mediante `$display_mincals = false;` en `config.inc.php` para expandir la grilla a pantalla completa (100% width).
     - Corrección del tamaño de fuente del año y encabezado de fechas (`h2.date` / `cur-year`).
     - Incorporación de estilos y clases de color para la totalidad de carreras y unidades institucionales (A–Y, E, I), eliminando los badges en blanco.
  5. **Adaptabilidad Móvil Completa (Mobile First & Touch-Friendly):**
     - Cabecera adaptable con apilamiento vertical y escala reducida para pantallas < 768px.
     - Contenedor con scroll horizontal táctil suave (`-webkit-overflow-scrolling: touch`) para navegar la semana.
     - **Sticky Column para módulos/horarios:** Fijación de la primera columna horaria (`08:15`, `09:00`...) a la izquierda durante el scroll lateral para mantener siempre visible la hora de clase.
     - Botones y selects agrandados a más de 40px de área táctil.
     - Inputs en formularios con `font-size: 16px` para prevenir el zoom no deseado de Safari en iPhone/iPad.
- **Resultado:** Interfaz visualmente renovada, 100% responsive, pantalla completa sin columnas laterales residuales, leyenda con paleta completa y coherente.

---

### [2026-11-06] - Fase 3: Tooltips Flotantes con Micro-Información Institucional (Hover Cards)
- **Objetivo:** Brindar contexto inmediato de reservas al pasar el cursor sin obligar al usuario a hacer clic ni salir de la pantalla actual (Opción A).
- **Acciones realizadas:**
  1. **Enriquecimiento de datos en `functions_table.inc` (`draw_cell`):**
     - Inyección de atributos de datos en cada bloque de reserva: `data-name`, `data-room` (`get_room_name()`), `data-time` (`hour_min()`), `data-user`, `data-type`, `data-type-name` (`get_type_vocab()`) y `data-desc`.
  2. **Creación de componente JavaScript interactivo (`Themes/udec/custom.js`):**
     - Detección de eventos `mouseenter` y `mouseleave` sobre bloques de reserva en la grilla.
     - Posicionamiento dinámico e inteligente (previene desbordes por bordes superiores, inferiores o laterales de la pantalla).
     - Supresión del tooltip nativo tosco del navegador.
     - Compatibilidad con recargas asíncronas de grilla vía AJAX (`ajaxComplete`).
  3. **Estilos institucionales de la tarjeta (`Themes/udec/custom.css`):**
     - Cabecera en azul marino institucional UdeC (`#002B49`) con insignia de carrera con su color correspondiente.
     - Íconos Font Awesome para horario exacto, recinto, docente/solicitante y observaciones.
     - Elevación y animación suave de entrada/salida (`opacity`, `transform: translateY`).
  4. **Registro en `config.inc.php`:**
     - Activación del script con `$custom_js_url = "Themes/udec/custom.js";`.
- **Resultado:** Visualización inmediata, fluida y estética de toda la información de la reserva al posar el cursor, sin recargar la página.

---

### [2026-11-06] - Fase 4: Leyenda Interactiva y Filtrado Dinámico de Carreras (Opción B)
- **Objetivo:** Permitir a Jefes de Carrera, Docentes y Administradores auditar y aislar visualmente en 1 clic el horario de cualquier carrera o unidad académica.
- **Acciones realizadas:**
  1. **Lógica de interacción en `Themes/udec/custom.js` (`initInteractiveLegend`):**
     - Inyección dinámica de cabecera en `.color_key` con título ("Filtrar horario por carrera") y botón de restablecimiento ("Ver todas").
     - Detección de clics en badges de carreras (`data-type-code`).
     - Alternancia interactiva: al hacer clic en una carrera (ej. *Auditoría*, *Ingeniería Comercial*, *Enfermería*), se aplica la clase `.active-filter` al badge seleccionado y se atenúan los demás badges.
     - En la grilla horaria (`.dwm_main`), todas las reservas coincidentes se iluminan y destacan con la clase `.highlighted-by-filter` (escala `1.04`, sombra tridimensional y animación de pulso institucional `udecPulseBadge`), mientras que las reservas ajenas se atenúan al 12% de opacidad (`.dimmed-by-filter`) con escala reducida y escala de grises.
     - Segundo clic o botón de reseteo restablece la vista completa instantáneamente.
     - Scroll automático suave hacia la tabla si el usuario se encuentra al final de la página.
  2. **Estilos de filtrado en `Themes/udec/custom.css`:**
     - Botón de reseteo con estilo UdeC (`#002B49` con borde dorado `#E89D1E`).
     - Checkmark automático `✓` en el badge de la carrera activa.
     - Animación CSS `@keyframes udecPulseBadge` alternando sombras doradas y azul marino.
- **Resultado:** Experiencia de auditoría horaria ágil, moderna y sin recarga de página para directivos y docentes.

---

### [2026-11-06] - Fase 5: Extensión de Hover Cards y Filtrado Interactivo a la Vista de Mes
- **Diagnóstico:** En la vista mensual (`view=month`), las reservas se generaban dentro de un contenedor diferente (`.booking_list`) sin la clase `.booking` ni los atributos `data-*` de enriquecimiento, lo que provocaba que ni las Hover Cards ni el filtrado por carrera funcionaran en dicha vista.
- **Acciones realizadas:**
  1. **Enriquecimiento de datos en `functions_table.inc` (`month_table_innerhtml`):**
     - Se añadió la clase unificadora `booking` a cada celda de reserva mensual.
     - Se inyectaron los mismos atributos ricos: `data-name`, `data-room`, `data-time`, `data-user`, `data-type`, `data-type-name` y `data-desc`.
  2. **Adaptación de selectores en `Themes/udec/custom.js`:**
     - El selector de Hover Cards ahora incluye `.booking_list a`, `.booking_list > div a` e `#month_main .booking a`.
     - El algoritmo de filtrado y atenuación ahora recorre uniformemente tanto las grillas de día/semana (`#day_main`, `#week_main`) como la grilla mensual (`#month_main`).
  3. **Estilos de la vista de mes en `Themes/udec/custom.css`:**
     - Estilización institucional de los bloques en `.booking_list` con esquinas redondeadas, truncado elíptico prolijo y sombras suaves.
     - Reglas de realce (`.highlighted-by-filter`) y atenuación (`.dimmed-by-filter`) adaptadas a la escala compacta de la cuadrícula mensual.
- **Resultado:** Compatibilidad 100% homogénea: Tooltips inteligentes y filtrado por carrera funcionan de forma idéntica en las 3 vistas (Día, Semana y Mes).

---

### [2026-11-06] - Fase 6: Barra de Filtros Rápidos de Salas y Equipamiento (Opción C)
- **Objetivo:** Permitir a los usuarios encontrar y aislar salas en tiempo real según Aforo (+30, +50, +100 pers.), Equipamiento (Proyector/Pantalla, Computadores ASUS/AIO), Tipo de recinto (Auditorios, Laboratorios) o Búsqueda por texto directo, sin recargar la página.
- **Acciones realizadas:**
  1. **Enriquecimiento estructural en `functions_table.inc`:**
     - Modificación de `room_cell_html()` para inyectar `data-room`, `data-room-name`, `data-capacity` y `data-description` en las cabeceras `<th>` de cada sala.
     - Modificación de las filas de recintos (`$tbody .= "<tr data-room=\"{$room['id']}\">\n";`) en vistas donde las salas son filas (ej. semana con `<Todas>` las salas).
     - Modificación de `cell_html()` pasando `array('room' => $room_id)` a `tdcell()` para que cada celda de la grilla esté vinculada a su sala correspondiente.
  2. **Implementación de componente interactivo en `Themes/udec/custom.js` (`initRoomFilters`):**
     - Barra de chips flotante inyectada dinámicamente debajo de `#dwm_header`.
     - Selector de aforo mínimo (+30, +50, Auditorios +100).
     - Chips toggle para "Con Proyector / TV", "Con Computadores", "Auditorios" y "Laboratorios".
     - Buscador de texto reactivo instantáneo (`input.udec-rf-search-input`).
     - Contador dinámico en vivo ("Mostrando X de Y salas en vista").
     - Botón de limpieza rápida de filtros ("Limpiar filtros").
     - Algoritmo de filtrado bidireccional: oculta/muestra filas completas de salas cuando son renglones (`tr[data-room]`) o columnas completas (`th[data-room]` y `td[data-room]`).
  3. **Diseño y Estilos en `Themes/udec/custom.css`:**
     - Barra estilizada con estética de tarjeta institucional UdeC, chips interactivos con íconos Font Awesome y transiciones suaves.
- **Resultado:** Localización ágil e instantánea de espacios según requisitos docentes y aforo en menos de 2 segundos.

---

### [2026-11-06] - Fase 7: Corrección de Formato Pantalla Completa y Reubicación Superior de Filtros
- **Diagnóstico de Incidencia:**
  - En la vista de sala individual, el calendario aparecía comprimido al 50% en la mitad derecha con un gran vacío blanco a la izquierda que contenía la barra de filtros y la insignia "0 salas".
  - **Causa raíz:** En la regla CSS heredada de MRBS (`mrbs.css.php`), `.index :not(.simple) + .contents` tenía configurado `display: flex;`. Al haberse insertado la barra de filtros con `.before($('.view_container'))`, se transformó en un elemento hermano directo de flexbox junto a `.view_container`, dividiendo la pantalla en dos columnas y encogiendo la grilla del calendario.
- **Acciones realizadas:**
  1. **Restablecimiento estricto de Pantalla Completa (100% Width):**
     - En `Themes/udec/custom.css`, se forzó `.index :not(.simple) + .contents`, `.contents` y `.udec-contents` a `display: block !important; width: 100% !important; max-width: 100% !important; float: none !important;`.
     - Se aseguró que `.view_container`, `.table_container` y las tablas de grilla (`table.dwm_main`, `#day_main`, `#week_main`, `#month_main`) se expandan al 100% de la pantalla sin divisiones laterales.
  2. **Reubicación superior e interna de la barra de filtros (`Themes/udec/custom.js`):**
     - La barra de filtros (`#udecRoomFilterBar`) se inserta ahora de forma estricta **arriba** de la tabla del calendario (inmediatamente antes de `.table_container`), dentro del flujo natural de `.view_container`.
     - Se agregó una verificación de reubicación automática por si el DOM ya contenía la barra desfasada.
  3. **Comportamiento contextual inteligente según tipo de vista:**
     - Si la vista actual es de una sala individual (`totalRooms <= 1`, como la vista semanal de un auditorio específico), la barra de filtros de salas se oculta automáticamente para no saturar la pantalla ni mostrar contadores innecesarios.
     - Si la vista actual contiene múltiples salas (ej. vista de área con `<Todas>` las salas en filas o columnas), la barra de filtros se despliega horizontalmente a pantalla completa (100% width) en la parte superior, entre los controles de navegación institucional y la grilla de horarios.
- **Resultado:** Calendario a pantalla completa impecable, sin columnas en blanco ni divisiones, y con la barra de filtros de salas integrada limpiamente en la parte superior cuando corresponde.

---

### [2026-11-06] - Fase 8: Unificación Inferior de Panel de Filtros y Búsqueda de Salas
- **Objetivo:** Reubicar la barra de filtros rápidos en la parte inferior del calendario, integrándola armónicamente con el menú de cambio de salas existente y la leyenda interactiva de carreras.
- **Acciones realizadas:**
  1. **Reubicación inferior en `Themes/udec/custom.js` (`initRoomFilters`):**
     - La barra de filtros de salas (`#udecRoomFilterBar`) se ubica ahora en la parte inferior de la página, inmediatamente antes de la leyenda de carreras (`.color_key`) y justo debajo de la navegación inferior del calendario.
  2. **Compatibilidad con vistas individuales y múltiples:**
     - En vistas con múltiples salas (`<Todas>`): la barra filtra activamente en tiempo real por aforo, proyector, computadores, auditorios, laboratorios y texto.
     - En vistas de sala individual (ej. *Auditorio Manuel Rioseco*): la barra muestra el nombre de la sala activa en su insignia y agrega un botón interactivo de 1 clic **"Ver todas las salas"** que conmuta de forma automática el selector de salas nativo a `<Todas>` para permitir la búsqueda y comparación global.
  3. **Jerarquía visual limpia:**
     - Arriba: Cabecera institucional + Navegación superior compacta + Grilla a pantalla completa (100% width).
     - Abajo: Navegación inferior + Filtros Rápidos de Salas + Leyenda de Carreras interactiva.
- **Resultado:** Interfaz superior limpia y despejada, con todo el centro de filtrado (Salas + Carreras) unificado prolijamente en el pie del calendario.

---

### [2026-11-06] - Fase 9: Retiro Limpio de la Barra de Filtros de Salas (Opción C)
- **Objetivo:** Desestimar y remover por completo el componente de Filtros Rápidos de Salas (Opción C) a petición del usuario, preservando la limpieza, estabilidad y velocidad de la interfaz.
- **Acciones realizadas:**
  1. **Limpieza en `Themes/udec/custom.js`:**
     - Eliminación de la llamada e inicialización de `initRoomFilters()` en `$(document).ready` y `ajaxComplete`.
     - Supresión total de las funciones `initRoomFilters()`, `applyRoomFilters()` y `testRoomCriteria()`.
     - Inyección de salvaguarda para eliminar cualquier remanente del nodo `#udecRoomFilterBar`.
  2. **Limpieza en `Themes/udec/custom.css`:**
     - Eliminación completa de la sección de estilos de la barra de filtros (`.udec-room-filter-bar`, chips, selects, botones de reset y contador).
  3. **Estado final de las funcionalidades activas:**
     - ✅ **Tema Institucional UdeC:** Paleta oficial azul marino (`#002B49`) y dorado (`#E89D1E`), tipografía Inter, bordes redondeados y tarjetas estilizadas.
     - ✅ **Pantalla Completa (100% Width):** Calendario expandido horizontalmente sin divisiones ni espacios muertos laterales.
     - ✅ **Opción A (Hover Cards):** Tarjetas flotantes interactivas con horario exacto, docente, carrera y observaciones al posar el cursor sobre cualquier reserva (Día, Semana y Mes).
     - ✅ **Opción B (Leyenda Interactiva):** Filtrado y resaltado de horario por carrera en 1 clic con atenuación de reservas ajenas y botón de restablecimiento.
- **Resultado:** Interfaz minimalista, sobria, sin saturación visual y con excelente rendimiento.

---

### [2026-11-06] - Fase 10: Reordenamiento de Elementos en Cabecera Institucional
- **Objetivo:** Intercambiar la posición de los botones de sesión/ayuda (`Ayuda` y `Entrar`) con la insignia de estado `Periodo Académico Activo` en la barra superior.
- **Acciones realizadas:**
  1. **Ajuste de marcado en `Themes/udec/header.inc` (`print_banner`):**
     - Se reordenó la emisión de los contenedores en el flujo de navegación:
       1. `udec-goto-wrap`: Selector de fecha (`page_date`) y búsqueda rápida.
       2. `udec-period-badge`: Insignia institucional con punto verde neón de "Periodo Académico Activo" (ubicada en el centro).
       3. `udec-menu-wrap`: Enlaces institucionales (`Ayuda`) y botón de acceso (`Entrar` / Nombre de usuario con sesión iniciada) al extremo derecho.
  2. **Control flexbox en `Themes/udec/custom.css`:**
     - Se fijó `flex-direction: row !important; justify-content: flex-end !important;` en `.banner nav.container` para neutralizar el `row-reverse` heredado de MRBS.
     - Se asignaron propiedades de orden explícitas (`order: 1`, `order: 2`, `order: 3`) garantizando consistencia en todos los navegadores.
- **Resultado:** Cabecera con jerarquía visual ergonómica: el acceso de sesión y ayuda queda anclado en la esquina superior derecha (estándar web UX), con el estado del período académico situado al centro.

---

### [2026-11-06] - Fase 11: Línea de Hora Actual en Vivo ("Ahora Mismo")
- **Objetivo:** Incorporar un indicador temporal visual en tiempo real que permita a docentes, estudiantes y personal de pañol identificar instantáneamente qué clase o reserva está ocurriendo en el momento exacto.
- **Acciones realizadas:**
  1. **Algoritmo de cálculo temporal en `Themes/udec/custom.js` (`initCurrentTimeLine`):**
     - Detección automática de la columna del día de hoy (`thead th[data-date]` en vista semanal o `#day_main` en vista diaria).
     - Mapeo matemático del tiempo actual (`horas * 60 + minutos`) contra los intervalos de cada bloque de clase (períodos de 45 min y recesos pedagógicos) para calcular con precisión de píxel la posición vertical `Y`.
     - Actualización periódica en vivo cada 60 segundos sin recargar la página (`setInterval`), y respuesta inmediata al redimensionar la ventana (`resize`).
     - Ocultación inteligente fuera de horario académico (antes de las 08:00 o después de las 20:30) y en vistas donde no aplica (como la vista mensual).
  2. **Diseño y Estilos en `Themes/udec/custom.css`:**
     - **Insignia flotante:** Badge carmesí (`#E11D48`) con la hora actual (`HH:MM`) y punto blanco con animación de pulso suave (`@keyframes udecPulseDot`).
     - **Línea de tiempo:** Línea horizontal de 2px con resplandor sutil que cruza la columna del día de hoy.
     - **Resaltado de columna:** Cabecera del día de hoy (`th.today`) con fondo suave azul marino y borde inferior dorado institucional UdeC (`#E89D1E`).
- **Resultado:** Experiencia moderna tipo Google Calendar / Apple Calendar que entrega contexto temporal inmediato de las clases en curso.

---

### [2026-11-06] - Fase 12: Empaquetado y Preparación de Despliegue para Servidor
- **Objetivo:** Generar un paquete autónomo y estructurado con todos los archivos modificados y nuevos para facilitar la subida a producción mediante FTP, SFTP o cPanel.
- **Acciones realizadas:**
  1. **Creación de la carpeta `Archivos_Para_Subir_Servidor/`:**
     - `Themes/udec/` (`custom.css`, `custom.js`, `header.inc`, `styling.inc`)
     - `images/` (`escudo-udec.png`)
     - `css/` (`mrbs.css.php`)
     - `functions_table.inc`
     - `config.inc.php`
     - `LEEME_INSTRUCCIONES.txt`
  2. **Generación del archivo comprimido:**
     - Creación de `Archivos_Para_Subir_Servidor.zip` (69 KB) listo para cargar y extraer en 1 paso en cPanel o FileZilla.
- **Resultado:** Paquete de entrega listo para el cliente/servidor con cero riesgo para la base de datos preexistente.

---

### [2026-11-07] - Fase 13: Implementación del Campo Personalizado 'Docente / Solicitante'
- **Objetivo:** Permitir ingresar en el formulario de reservas el nombre del docente o persona que realmente solicita el recinto (independiente del usuario que opera el sistema), reflejándolo automáticamente en las Hover Cards y en el detalle de la reserva.
- **Acciones realizadas:**
  1. **Migración de Base de Datos:**
     - Adición de la columna `solicitante VARCHAR(150) NULL DEFAULT ''` en las tablas `mrbs_entry` (reservas puntuales) y `mrbs_repeat` (reservas periódicas semestrales).
  2. **Configuración en `config.inc.php`:**
     - Definición de etiquetas personalizadas: `$vocab_override["es"]["entry.solicitante"] = "Docente / Solicitante";`.
     - Priorización del campo en el formulario de creación/edición mediante `$edit_entry_field_order = array('name', 'solicitante', 'description', 'start_time', 'end_time', 'room_id', 'type');`.
  3. **Integración con Hover Cards y Vista de Mes (`functions_table.inc`):**
     - En `draw_cell()` y `month_table_innerhtml()`, el atributo `data-user` lee prioritariamente `$booking['solicitante']` si existe; si está vacío (en reservas históricas), utiliza `$booking['create_by']` como respaldo sin romper datos previos.
  4. **Actualización del paquete de despliegue:**
     - Archivos `config.inc.php` y `functions_table.inc` actualizados en `Archivos_Para_Subir_Servidor/` y reempaquetados en `Archivos_Para_Subir_Servidor.zip`.
     - Instrucciones SQL agregadas en `LEEME_INSTRUCCIONES.txt`.
- **Resultado:** Formulario con campo dedicado para el docente o solicitante real, visible en Hover Cards y en el reporte detallado de cada reserva.

---

### [2026-11-07] - Fase 14: Ajuste de Terminología Institucional y Filtro por Tipo de Horario
- **Objetivo:** Refinar las etiquetas visuales para adoptar una nomenclatura más amplia y neutra según requerimientos del usuario.
- **Acciones realizadas:**
  1. **Ajuste de campo 'Solicitante':**
     - En `config.inc.php`: etiqueta cambiada de `"Docente / Solicitante"` a únicamente `"Solicitante"`.
     - En `Themes/udec/custom.js`: en las Hover Cards se muestra ahora como `<strong>Solicitante:</strong>`.
  2. **Ajuste de título en la leyenda interactiva:**
     - En `Themes/udec/custom.js`: el encabezado se actualizó de `"Filtrar horario por carrera:"` a `"Filtro por tipo de horario:"`.
  3. **Actualización de empaquetado de producción:**
     - Sincronización en `Archivos_Para_Subir_Servidor/` y reconstrucción de `Archivos_Para_Subir_Servidor.zip`.
- **Resultado:** Terminología más limpia, concisa y adaptable tanto a reservas docentes como a eventos administrativos o externos.

---

### [2026-11-07] - Fase 15: Implementación del Panel Web Visual de Tipos y Colores (Para Administradores)
- **Objetivo:** Brindar a los administradores una interfaz web visual, moderna e interactiva para gestionar las carreras, unidades y tipos de reserva junto con sus colores institucionales, sin necesidad de modificar código fuente ni reiniciar servidores.
- **Acciones realizadas:**
  1. **Creación de `admin_types.php`:**
     - Página protegida por autenticación y RBAC (`checkAuthorised()` y `is_admin()`, nivel 2).
     - Tabla interactiva con inputs editables de código, nombre de carrera/unidad, selector visual de color (`<input type="color">`) sincronizado en tiempo real con input hexadecimal, badge de vista previa dinámica con cálculo automático de contraste YIQ, toggle switch para activar/desactivar y botón de eliminación.
     - Botón para agregar nuevas filas de forma dinámica con JavaScript.
     - Acciones de guardado persistente y botón de restauración a los 24 tipos/colores institucionales de UdeC.
  2. **Persistencia Dinámica en `Themes/udec/types_config.json`:**
     - Los datos se almacenan en un JSON estructurado y ligero.
     - `config.inc.php` carga dinámicamente `$booking_types` y `$vocab_override` desde este archivo.
     - `styling.inc` carga `$color_types` dinámicamente.
     - `header.inc` inyecta dinámicamente reglas CSS en `<style id="udec-types-dynamic-styles">` para actualización instantánea en el navegador sin problemas de caché.
  3. **Navegación e Integración de Menú:**
     - Se añadió el ítem `admin_types` con ícono `fa-palette` en la barra superior de navegación (`header.inc`), visible únicamente para administradores autenticados.
  4. **Estilos Institucionales UdeC en `Themes/udec/custom.css`:**
     - Tarjeta con borde dorado y navy UdeC, alertas de confirmación y switch toggle accesible.
  5. **Actualización del paquete de despliegue:**
     - Copia de `admin_types.php`, `types_config.json`, `header.inc`, `styling.inc`, `config.inc.php`, `custom.css` y `custom.js` a `Archivos_Para_Subir_Servidor/`.
     - Actualización de `LEEME_INSTRUCCIONES.txt` con instrucciones de permisos (`chmod 666`).
     - Reconstrucción de `Archivos_Para_Subir_Servidor.zip` (72 KB).
- **Resultado:** Los administradores pueden personalizar visualmente todas las carreras y colores del sistema en cualquier momento con cambios reflejados al instante en el calendario, las tarjetas flotantes y los filtros.

---

### [2026-11-08] - Fase 16: Corrección de Formato de Horarios en Hover Cards (Soporte Nativo de Periodos Académicos)
- **Problema detectado:** En recintos configurados con bloques o periodos académicos (`enable_periods = true`), las tarjetas emergentes (*Hover Cards*) mostraban horas anómalas (ej. `* 3:02 p. m. - * 3:03 p. m. hrs`) en lugar del bloque pedagógico real guardado (`09:50 - 10:35 hrs`). Esto ocurría porque el código anterior formateaba el timestamp epoch directamente con `hour_min()`, ignorando que en el modo de periodos de MRBS el minuto codifica el índice del bloque.
- **Acciones realizadas:**
  1. **Función auxiliar inteligente en `functions_table.inc`:**
     - Creación de `get_hover_time_slot_string()`: detecta si el recinto utiliza periodos (`enable_periods`) o modo de horas continuas.
     - En áreas con periodos, resuelve el nombre exacto del bloque con `period_time_string()` (ej. `09:50 - 10:35`) y maneja coherentemente reservas de un solo bloque o de múltiples bloques continuos (ej. `09:50 - 11:20`).
     - En áreas de horario libre, mantiene la conversión tradicional con `hour_min()`.
  2. **Actualización de llamadas en `cell_html()` y `month_table_innerhtml()`:**
     - Tanto en la grilla diaria/semanal como en la vista mensual, el atributo `data-time` se genera con `get_hover_time_slot_string()`.
  3. **Ajuste en `Themes/udec/custom.js`:**
     - Sanitización para evitar sufijos duplicados (`hrs hrs`).
  4. **Actualización del paquete de producción:**
     - Archivos `functions_table.inc` y `Themes/udec/custom.js` sincronizados en `Archivos_Para_Subir_Servidor/` y reconstruido el archivo `Archivos_Para_Subir_Servidor.zip`.
- **Resultado:** Las tarjetas flotantes (*Hover Cards*) muestran exactamente el bloque pedagógico o intervalo horario real guardado en la reserva (ej. `09:50 - 10:35 hrs`), en total concordancia con la grilla y la vista de detalle.

---

### [2026-11-08] - Fase 17: Implementación del Panel Administrativo de Reservas Pendientes y Sin Confirmar (`admin_pending.php`)
- **Objetivo:** Crear un espacio centralizado en administración para visualizar, filtrar, confirmar/aprobar o rechazar reservas pendientes (tanto en estado *Sin Confirmar / Tentativas* `status = 4` como *Por Aprobar* `status = 2`), mostrando por defecto aquellas desde la fecha actual en adelante (Opción A).
- **Acciones realizadas:**
  1. **Creación de `admin_pending.php`:**
     - Protegido por autenticación y permisos de administrador (`checkAuthorised()`, `is_admin()` / `is_book_admin()` y `Form::checkToken(true)`).
     - **Tarjetas KPI Interactivas:** Muestra conteos separados de *Total Solicitudes Pendientes*, *Reservas Puntuales / Únicas* y *Series Semestrales Agrupadas* (con filtro en 1 clic).
     - **Agrupación Inteligente de Series Semestrales:** Agrupa múltiples sesiones de una misma asignatura/serie (`repeat_id`) en una sola fila con su rango de fechas y cantidad de bloques, evitando saturar la vista con miles de filas repetidas.
     - **Filtros Combinables:** Horizonte temporal (*Desde hoy en adelante* por defecto, *Próximos 7 días*, *Próximos 30 días*, *Todas*), Modalidad (*Todas*, *Solo Puntuales*, *Solo Series*), Edificio/Área y buscador instantáneo en tiempo real.
     - **Acciones en 1 Clic y en Lote (Bulk Actions):** Confirmar reserva puntual, confirmar serie semestral completa, ir al día/sala exacto en el calendario, ver ficha detallada, o rechazar/eliminar (individual o masivamente con casillas de selección).
  2. **Integración en Barra Superior (`Themes/udec/header.inc` y `config.inc.php`):**
     - Nuevo botón **Pendientes** (`fa-clock-rotate-left`) en el menú superior para administradores, acompañado de un contador dinámico en vivo (`udec-pending-nav-badge`) que indica el total de solicitudes pendientes desde hoy en adelante.
  3. **Estilos Institucionales en `Themes/udec/custom.css`:**
     - Diseño de tarjetas KPI, barra de filtros, botones de acción rápida y badges de estado.
  4. **Actualización del paquete de despliegue:**
     - Sincronización de `admin_pending.php`, `config.inc.php`, `header.inc`, `custom.css` y `LEEME_INSTRUCCIONES.txt` en `Archivos_Para_Subir_Servidor/` y reconstrucción de `Archivos_Para_Subir_Servidor.zip`.
- **Resultado:** Los administradores cuentan con control total y ágil sobre las solicitudes pendientes y reservas tentativas desde un panel dedicado.

---

### [2026-11-08] - Fase 18: Actualización de Salas, Capacidades, Equipamiento e Incorporación de Nuevos Recintos (Excel Oficial)
- **Objetivo:** Sincronizar la base de datos (`mrbs_area` y `mrbs_room`) con el catastro oficial en `Documentos/Salas y edificios, cantidad y descripción.xlsx`, preservando intactas las áreas actuales y la totalidad de reservas históricas (`+28.500` registros).
- **Acciones realizadas:**
  1. **Script de Migración SQL Idempotente (`actualizar_salas_udec.sql` / `z_actualizar_salas_udec.sql`):**
     - Ampliación de la columna `mrbs_room.description` de `VARCHAR(60)` a `VARCHAR(150)` para soportar el detalle técnico de equipamiento sin truncamientos.
     - Actualización de capacidades oficiales y descripciones estructuradas (`Tipo: [Normal/Híbrida/AIO] | [Equipamiento activo]`) en las 45 salas preexistentes del Excel.
     - Normalización de nombres en Edificio Enrique Molina (`Sala 411`, `Sala 412`, `Sala 421`, `Sala 422`) y corrección ortográfica en Cowork (`Hall Principal`).
     - Creación de la nueva área **`Edificio de Laboratorios` (`id = 21`)** con bloques académicos institucionales.
     - Incorporación de **6 nuevos recintos**:
       - `Laboratorio Música` (Cap. 20, Edificio de Artes)
       - `Lab. de Artes` (Cap. 25, Edificio de Artes)
       - `Lab. Geomática` (Cap. 30, Pasillo Central)
       - `Lab 1` (Cap. 12, Edificio de Laboratorios)
       - `Lab 2` (Cap. 16, Edificio de Laboratorios)
       - `Lab 3` (Cap. 6, Edificio de Laboratorios)
     - Conservación activa (`disabled = 0`) de los espacios adicionales preexistentes (`Gimnasio`, `Pérgola`, `Lab. Ciencias del Trabajo`, etc.).
  2. **Cintillo Institucional de Ficha Técnica de Recinto (`functions_table.inc`, `custom.js`, `custom.css`):**
     - Al visualizar cualquier sala individual en vista Semanal o Mensual, se despliega automáticamente un cintillo superior con el **nombre del recinto**, su **Capacidad oficial** y sus **badges de Tipo de Sala y Equipamiento** (Proyector, Pantalla Táctil, PC, Audio AUX/BT, A/C, TV).
  3. **Actualización del paquete de producción:**
     - Inclusión de `actualizar_salas_udec.sql` y sincronización de `functions_table.inc`, `custom.js`, `custom.css` y `LEEME_INSTRUCCIONES.txt` en `Archivos_Para_Subir_Servidor/` y `Archivos_Para_Subir_Servidor.zip`.
- **Resultado:** Base de datos local y paquete de despliegue 100% actualizados con las capacidades, tecnologías y nuevos laboratorios del Campus Los Ángeles.

---

### [2026-11-08] - Fase 19: Consolidación en Repositorio GitHub y Paquete Completo para Nuevo Hosting (FTP + phpMyAdmin)
- **Objetivo:** Subir todo el proyecto y la base de datos consolidada al repositorio oficial https://github.com/fjpb/Sistema-Reserva-Espacios-UdeC y preparar una carpeta lista para desplegar el sistema desde cero en un nuevo hosting mediante FTP y phpMyAdmin.
- **Acciones realizadas:**
  1. **Consolidación de Base de Datos (`Hosting_Nuevo_FTP_phpMyAdmin/Base_de_Datos_phpMyAdmin/`):**
     - Generación de `unidadti_completa_udec.sql` (8.3 MB) y su versión comprimida `unidadti_completa_udec.sql.zip` (742 KB, ideal para importar en 1 clic en phpMyAdmin sin bloqueos por límite de tamaño de subida).
     - Incluye en un solo archivo las 9 tablas base, las +28.500 reservas históricas, la columna personalizada `solicitante` en `mrbs_entry` y `mrbs_repeat`, el nuevo `Edificio de Laboratorios` (`id = 21`) y las 68 salas actualizadas con capacidades y equipamiento.
     - Actualización de `sistemadereserva/DB/z_actualizar_salas_udec.sql` y `Archivos_Para_Subir_Servidor/actualizar_salas_udec.sql` para incluir también la creación idempotente de la columna `solicitante` y el ajuste de `AUTO_INCREMENT`.
  2. **Empaquetado Completo para Nuevo Hosting (`Hosting_Nuevo_FTP_phpMyAdmin/`):**
     - `Archivos_Web_FTP/` y `Archivos_Web_FTP.zip` (8.4 MB): contiene el 100% del sistema MRBS junto con el Tema Institucional UdeC, `admin_pending.php`, `admin_types.php`, Hover Cards y Ficha Técnica de Recintos.
     - `INSTRUCCIONES_HOSTING_FTP_Y_PHPMYADMIN.txt`: guía paso a paso para importar la BD en phpMyAdmin, configurar las credenciales en `config.inc.php`, subir por FTP y asignar permisos a `Themes/udec/types_config.json`.
  3. **Sincronización con GitHub:**
     - Creación de `.gitignore` y `README.md` raíz documentando la arquitectura y los pasos de despliegue.
     - Inicialización del repositorio Git y push completo a la rama `main` en `https://github.com/fjpb/Sistema-Reserva-Espacios-UdeC`.
- **Resultado:** Repositorio centralizado en GitHub y paquete integral listo para poner en marcha el sistema en cualquier nuevo hosting con FTP y phpMyAdmin.





