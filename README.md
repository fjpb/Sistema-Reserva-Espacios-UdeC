# 🏛️ Sistema de Reserva de Espacios Académicos — UdeC Campus Los Ángeles

Sistema institucional de reservas y gestión horaria de salas, laboratorios y recintos académicos para la **Universidad de Concepción — Campus Los Ángeles**, desarrollado sobre **MRBS** con personalización completa del **Tema Institucional UdeC**.

---

## 📁 Estructura del Repositorio

El repositorio contiene exclusivamente los componentes esenciales del sistema y su base de datos:

```text
├── sistemadereserva/
│   ├── mrbs/             # Aplicación web completa MRBS + Tema Institucional UdeC
│   │   ├── Themes/udec/  # Estilos, scripts, cabecera y configuración de carreras/colores
│   │   ├── admin_pending.php  # Panel de Reservas Pendientes y Sin Confirmar
│   │   ├── admin_types.php    # Panel Web de Gestión de Carreras y Colores
│   │   ├── config.inc.php     # Configuración general y conexión a BD
│   │   └── ...
│   │
│   ├── DB/               # Base de datos MySQL / MariaDB
│   │   ├── mbrs_0611unidadti.sql        # Volcado base con +28.500 reservas históricas
│   │   ├── z_actualizar_salas_udec.sql  # Script de salas, laboratorios y campo solicitante
│   │   └── phpmyadmin/
│   │       └── unidadti_completa_udec.sql.zip # Dump completo consolidado para phpMyAdmin
│   │
│   ├── Dockerfile        # Definición de contenedor Apache + PHP para MRBS
│   ├── docker-compose.yml# Despliegue local (Web en :8085, MariaDB en :3307, phpMyAdmin en :8086)
│   └── README.md
│
└── README.md
```

---

## ✨ Características Principales

1. **Tema Institucional UdeC (`Themes/udec/`):**
   - Escudo oficial UdeC y paleta cromática (Azul marino `#002E5D` y Dorado `#EAAA00`).
   - **Hover Cards:** Tarjetas emergentes interactivas con horario pedagógico exacto, asignatura, docente/solicitante y estado.
   - **Línea de Tiempo en Vivo:** Marcador de hora y bloque pedagógico actual en la grilla.
   - **Ficha Técnica de Recinto:** Cintillo superior que muestra automáticamente capacidad oficial, tipo de sala y equipamiento activo (Proyector, Pantalla Táctil, PC, Audio, A/C, TV).
2. **Panel Administrativo de Pendientes (`admin_pending.php`):**
   - Aprobación y confirmación en 1 clic de reservas puntuales y series semestrales agrupadas.
   - Contador en vivo de pendientes en la barra de navegación superior.
3. **Panel Web Visual de Tipos y Colores (`admin_types.php`):**
   - Configuración gráfica de carreras, unidades y colores en tiempo real sin tocar código (`types_config.json`).
4. **Catastro Oficial Actualizado:**
   - 68 salas y recintos sincronizados con el catastro oficial, incorporando el **Edificio de Laboratorios** (`Lab 1`, `Lab 2`, `Lab 3`), `Laboratorio Música`, `Lab. de Artes` y `Lab. Geomática`.

---

## 🚀 Despliegue en Servidor / Hosting (FTP + phpMyAdmin)

1. **Importar la Base de Datos en phpMyAdmin:**
   - Crear una base de datos MySQL (ej. `unidadti`).
   - En phpMyAdmin, ir a **Importar** y seleccionar el archivo comprimido:
     `sistemadereserva/DB/phpmyadmin/unidadti_completa_udec.sql.zip` (742 KB).
2. **Configurar Credenciales:**
   - Abrir `sistemadereserva/mrbs/config.inc.php` y ajustar `db_host`, `db_database`, `db_login` y `db_password`.
3. **Subir Archivos por FTP:**
   - Subir el contenido de `sistemadereserva/mrbs/` al directorio web (`public_html`).
   - Asignar permisos de escritura (`chmod 666`) a `Themes/udec/types_config.json`.

---

## 🐳 Ejecución Local con Docker

```bash
cd sistemadereserva
docker compose up -d
```
- **Sistema MRBS:** [http://localhost:8085](http://localhost:8085)
- **phpMyAdmin Local:** [http://localhost:8086](http://localhost:8086)
