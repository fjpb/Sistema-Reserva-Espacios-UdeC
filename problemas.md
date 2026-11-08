# ⚠️ Bitácora de Problemas, Diagnósticos y Soluciones (`problemas.md`)
## Sistema de Gestión Horaria y Reserva de Espacios Académicos

Este documento registra los incidentes, bloqueos técnicos, fallos de compatibilidad y anomalías de ejecución detectadas en el sistema, junto con su causa raíz y la solución definitiva aplicada.

---

## 📋 Índice de Incidencias

1. [INC-001: Denegación de acceso al socket de Docker en entorno restringido](#inc-001-denegación-de-acceso-al-socket-de-docker-en-entorno-restringido)
2. [INC-002: Error de locale `es-419` y fallo en inicialización de sesiones en Safari](#inc-002-error-de-locale-es-419-y-fallo-en-inicialización-de-sesiones-en-safari)
3. [INC-003: Colisión potencial de puerto 8080 en el host](#inc-003-colisión-potencial-de-puerto-8080-en-el-host)

---

### INC-001: Denegación de acceso al socket de Docker en entorno restringido

- **Fecha:** 2026-11-06
- **Severidad:** Media (Bloqueo de ejecución en herramientas automáticas)
- **Componentes:** Docker CLI / macOS `/var/run/docker.sock`

#### Síntoma:
Al intentar ejecutar `docker ps` o `docker compose up`, el comando fallaba con el siguiente error:
```text
permission denied while trying to connect to the docker API at unix:///var/run/docker.sock
```

#### Causa Raíz:
El sandbox por defecto restringe el acceso a descriptores de archivo fuera del espacio de trabajo del usuario. El socket Unix de Docker Desktop para macOS se ubica en `/var/run/docker.sock`.

#### Solución Aplicada:
Se ejecutaron los comandos de orquestación de Docker con permisos elevados del sistema (`BypassSandbox: true`), permitiendo la comunicación directa con el Docker Daemon.

---

### INC-002: Error de locale `es-419` y fallo en inicialización de sesiones en Safari

- **Fecha:** 2026-11-06
- **Severidad:** Alta (La aplicación no cargaba su interfaz visual ni sus estilos CSS en navegadores macOS/iOS)
- **Componentes:** `mrbs/language.inc`, `mrbs/lib/MRBS/Session/Session.php`, Apache/PHP 8.2

#### Síntoma:
Al abrir [http://localhost:8085](http://localhost:8085) en Safari, la pantalla se mostraba en blanco con los siguientes mensajes de error en texto plano:
```text
Notice: Server failed to set locale to ["es-419.UTF-8","es_419.UTF-8"] for language tag 'es-419'. 
Either install the missing locale or set $override_locale in your MRBS config.inc.php file to a locale that is available on your server. in /var/www/html/language.inc on line 179

E_WARNING in /var/www/html/lib/MRBS/Session/Session.php at line 55
ini_set(): Session ini settings cannot be changed after headers have already been sent
```

#### Causa Raíz:
1. **Diferencia entre etiquetas IETF y Locales POSIX:** Safari en macOS envía de forma nativa la cabecera `Accept-Language: es-419` (*Español de Latinoamérica y el Caribe*). En sistemas operativos Linux/Debian no existe el locale POSIX glibc `es_419.UTF-8` (solo existen códigos por país como `es_CL`, `es_AR`, `es_ES`, etc.).
2. **Emisión temprana de salida:** Al no encontrar dicho locale y no tener `$override_locale` configurado, MRBS ejecutaba `trigger_error(..., E_USER_NOTICE)`.
3. **Buffer de salida inactivo (`output_buffering = 0`):** Al tener el buffer en 0 y `display_errors = On`, el aviso se imprimió en stdout antes de que PHP invocara `Session::init()`.
4. **Cabeceras ya enviadas:** Toda modificación posterior de cookies y parámetros de sesión (`ini_set('session.cookie_httponly', ...)`) fue rechazada por PHP porque las cabeceras HTTP ya habían sido despachadas al navegador.

#### Solución Aplicada:
1. **Configuración de Locale Forzado en MRBS:**
   En [`sistemadereserva/mrbs/config.inc.php`](file:///Users/FJ/Desktop/Proyectos%20IA/Reservas%20de%20Salas%20Gestión%20Horaria/sistemadereserva/mrbs/config.inc.php):
   ```php
   $timezone = "America/Santiago";
   $override_locale = "es-CL";
   $default_language_tokens = "es";
   ```
2. **Generación de Locales en el Contenedor:**
   En [`sistemadereserva/Dockerfile`](file:///Users/FJ/Desktop/Proyectos%20IA/Reservas%20de%20Salas%20Gestión%20Horaria/sistemadereserva/Dockerfile):
   ```dockerfile
   RUN apt-get update && apt-get install -y --no-install-recommends locales ...
   RUN echo "es_CL.UTF-8 UTF-8" >> /etc/locale.gen && locale-gen
   ```
3. **Protección de Cabeceras en `php.ini` (`mrbs-custom.ini`):**
   ```ini
   output_buffering = 4096
   display_errors = Off
   log_errors = On
   ```
- **Verificación:** Pruebas automatizadas con `curl -H "Accept-Language: es-419,es;q=0.9"` retornaron `HTTP/1.1 200 OK`, sin advertencias y con cookies de sesión `SameSite=Strict; HttpOnly` correctamente entregadas.

---

### INC-003: Colisión potencial de puerto 8080 en el host

- **Fecha:** 2026-11-06
- **Severidad:** Baja (Preventiva)
- **Componentes:** Docker Engine / Networking

#### Síntoma:
El puerto estándar `8080` ya se encontraba ocupado en la máquina anfitriona por un contenedor WordPress preexistente (`fjpb_wordpress_app`).

#### Causa Raíz:
Multiplicidad de proyectos de desarrollo local compartiendo el mismo puerto estándar HTTP alternativo.

#### Solución Aplicada:
Se mapearon puertos no conflictivos y predecibles en [`sistemadereserva/docker-compose.yml`](file:///Users/FJ/Desktop/Proyectos%20IA/Reservas%20de%20Salas%20Gestión%20Horaria/sistemadereserva/docker-compose.yml):
- **MRBS Web:** Puerto `8085` -> Contenedor `80`
- **phpMyAdmin:** Puerto `8086` -> Contenedor `80`
- **MariaDB:** Puerto `3307` -> Contenedor `3306`
