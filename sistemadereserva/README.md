# Entorno Docker para sistemadereserva (MRBS)

Este entorno Docker permite ejecutar y desarrollar localmente el sistema de reservas **MRBS** con su base de datos MariaDB pre-cargada.

---

## 🚀 Servicios y URLs de Acceso

| Servicio | URL / Host | Puerto | Credenciales |
| :--- | :--- | :--- | :--- |
| **Sistema de Reservas (Web)** | [http://localhost:8085](http://localhost:8085) | `8085` | Usuarios registrados en la BD |
| **phpMyAdmin** | [http://localhost:8086](http://localhost:8086) | `8086` | **Usuario:** `unidadti`<br>**Contraseña:** `6gsi26SzTZzmwRQW` |
| **Base de Datos MariaDB** | `localhost` | `3307` | **Usuario:** `unidadti`<br>**Contraseña:** `6gsi26SzTZzmwRQW`<br>**BD:** `unidadti` |

---

## 🛠️ Comandos de Uso

Ejecuta estos comandos dentro de la carpeta `sistemadereserva/`:

### Iniciar contenedores en segundo plano:
```bash
docker compose up -d
```

### Detener los contenedores:
```bash
docker compose down
```

### Ver logs en tiempo real:
```bash
docker compose logs -f
```

### Reiniciar el contenedor web:
```bash
docker compose restart web
```

---

## 💻 Desarrollo en Local (Live Reload)

La carpeta `./mrbs` está montada como volumen dentro del contenedor web (`/var/www/html`). Cualquier cambio que realices en el código PHP, CSS, JavaScript o plantillas se verá reflejado **inmediatamente** en tu navegador sin necesidad de reconstruir la imagen de Docker.

---

## 🗄️ Persistencia de la Base de Datos

- En la primera inicialización, MariaDB carga automáticamente el volcado ubicado en `DB/mbrs_0611unidadti.sql`.
- Los datos se guardan de forma persistente en el volumen Docker `sistemadereserva_db_data`.
