# 🏛️ Sistema de Reserva de Espacios Académicos — UdeC Campus Los Ángeles

Plataforma institucional de gestión horaria y reserva de salas, laboratorios y auditorios para la **Universidad de Concepción — Campus Los Ángeles**, basada en **MRBS** con personalización integral **Tema Institucional UdeC**.

---

## ✨ Características Principales Implementadas

1. **Diseño Institucional UdeC (`Themes/udec/`):**
   - Cabecera oficial con escudo UdeC, paleta cromática institucional (Navy `#002E5D` y Dorado `#EAAA00`).
   - **Línea de Tiempo en Vivo:** Indicador visual de hora/módulo actual en el calendario.
   - **Tarjetas Flotantes (*Hover Cards*):** Vista rápida de asignatura, solicitante, sala, bloque horario exacto y estado sin recargar la página.
   - **Cintillo de Ficha Técnica de Recinto:** Muestra automáticamente capacidad oficial, tipo de sala (*Normal, Híbrida, AIO*) y equipamiento tecnológico (*Proyector, Pantalla Táctil, PC, Audio AUX/BT, A/C, TV*).
2. **Panel Administrativo de Reservas Pendientes (`admin_pending.php`):**
   - Gestión en 1 clic de reservas *Sin Confirmar / Tentativas* y *Por Aprobar*.
   - Agrupación inteligente de series semestrales, tarjetas KPI interactivas, filtros combinables y acciones por lote (*Bulk Confirm / Delete*).
   - Contador dinámico en vivo en la barra superior de navegación.
3. **Panel Visual Web de Tipos y Colores (`admin_types.php`):**
   - Administración en vivo de carreras, unidades y colores institucionales con selector HTML5 y persistencia en `Themes/udec/types_config.json`.
   - Leyenda interactiva en el calendario con filtro instantáneo por carrera/unidad.
4. **Catastro Oficial de Salas y Laboratorios Actualizado:**
   - Capacidades y equipamiento sincronizados con el catastro oficial del Campus Los Ángeles, incluyendo el nuevo **Edificio de Laboratorios** (`Lab 1`, `Lab 2`, `Lab 3`), `Laboratorio Música`, `Lab. de Artes` y `Lab. Geomática`.

---

## 📂 Estructura del Repositorio

| Carpeta / Archivo | Descripción |
| :--- | :--- |
| [`Hosting_Nuevo_FTP_phpMyAdmin/`](./Hosting_Nuevo_FTP_phpMyAdmin/) | **Paquete listo para subir a un Nuevo Hosting (FTP + phpMyAdmin)**. Incluye todos los archivos web (`Archivos_Web_FTP/`), el archivo comprimido `Archivos_Web_FTP.zip`, la base de datos unificada lista para importar (`unidadti_completa_udec.sql.zip`) y la guía paso a paso. |
| [`sistemadereserva/`](./sistemadereserva/) | Entorno completo con **Docker & Docker Compose** (`mrbs/`, `DB/`, `Dockerfile`, `docker-compose.yml`) para desarrollo y pruebas locales. |
| [`Archivos_Para_Subir_Servidor/`](./Archivos_Para_Subir_Servidor/) | Paquete ligero únicamente con los archivos modificados/nuevos (útil para actualizar un servidor MRBS preexistente). |
| [`Documentos/`](./Documentos/) | Catastro oficial de salas/edificios (`.xlsx`) e informes de gestión horaria. |
| [`history.md`](./history.md) | Bitácora técnica detallada de todas las fases de desarrollo e implementación. |

---

## 🚀 Despliegue Rápido en Nuevo Hosting (FTP + phpMyAdmin)

Consulta la guía detallada en [`Hosting_Nuevo_FTP_phpMyAdmin/INSTRUCCIONES_HOSTING_FTP_Y_PHPMYADMIN.txt`](./Hosting_Nuevo_FTP_phpMyAdmin/INSTRUCCIONES_HOSTING_FTP_Y_PHPMYADMIN.txt):

1. **Base de Datos (phpMyAdmin):**
   - Crea una base de datos y usuario MySQL en tu hosting.
   - Entra a **phpMyAdmin -> Importar** y sube el archivo:
     `Hosting_Nuevo_FTP_phpMyAdmin/Base_de_Datos_phpMyAdmin/unidadti_completa_udec.sql.zip` (742 KB).
2. **Configuración (`config.inc.php`):**
   - Edita `Hosting_Nuevo_FTP_phpMyAdmin/Archivos_Web_FTP/config.inc.php` (líneas 58-73) con el host, nombre de base de datos, usuario y contraseña de tu nuevo hosting.
3. **Subida por FTP:**
   - Sube el contenido de `Hosting_Nuevo_FTP_phpMyAdmin/Archivos_Web_FTP/` (o descomprime `Archivos_Web_FTP.zip`) en la carpeta `public_html` de tu servidor.
   - Asigna permisos de escritura (`chmod 666`) al archivo `Themes/udec/types_config.json`.

---

## 🐳 Ejecución Local con Docker

```bash
cd sistemadereserva
docker compose up -d
```
- **Sistema Web MRBS:** `http://localhost:8085`
- **phpMyAdmin Local:** `http://localhost:8086`
