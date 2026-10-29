# API de Autenticación — SENA GA7-220501096-AA5-EV01

Servicio web en PHP para registrar usuarios e iniciar sesión. Sin frameworks ni Composer:
router propio, conexión con PDO y respuestas en JSON.

Evidencia de aprendizaje del programa **Análisis y Desarrollo de Software (ADSO)** del SENA.

## Qué hace

| Método | Ruta | Descripción |
|---|---|---|
| `POST` | `/register` | Crea un usuario con la contraseña hasheada (bcrypt) |
| `POST` | `/login` | Valida las credenciales de un usuario |
| `GET` | `/` | Devuelve la información del servicio y sus endpoints |

Todas las respuestas son JSON con la forma `{"estado": "...", "mensaje": "..."}`, acompañadas
del código HTTP correspondiente.

## Alcance (importante)

La API **valida credenciales, pero no emite tokens**. Es decir: responde si la contraseña es
correcta, y ahí termina; no hay JWT ni sesión que el cliente pueda usar para autenticar
peticiones posteriores.

Era el alcance pedido en la evidencia. Se documenta explícitamente porque es lo primero que va
a notar alguien que lea el código esperando una autenticación completa.

## Stack

- **PHP 8+** con PDO
- **MySQL / MariaDB**
- **Apache** con `mod_rewrite` (para las rutas amigables)
- Sin dependencias externas: no requiere Composer ni `vendor/`

## Requisitos

- XAMPP, WAMP o Laragon con Apache y MySQL
- `mod_rewrite` habilitado (en XAMPP viene activo por defecto)

## Instalación

**1. Clonar el proyecto en `htdocs`**

```bash
cd C:\xampp\htdocs
git clone https://github.com/joelcastrodev/auth_api_sena.git
```

El nombre de la carpeta no importa: el router trabaja con el último segmento de la URL, así
que funciona igual como `auth_api_sena`, como `auth_api` o en la raíz de un dominio.

**2. Importar la base de datos**

En phpMyAdmin (`http://localhost/phpmyadmin`), importar `sql/auth_api.sql`. El script crea la
base de datos `auth_api` con la tabla `usuarios`.

**3. Revisar la conexión**

`config/database.php` viene para el entorno por defecto de XAMPP (`localhost`, usuario `root`,
sin contraseña, base `auth_api`). Ajustar si tu MySQL es distinto.

**4. Comprobar que responde**

Abrir en el navegador:

```
http://localhost/auth_api_sena/
```

Debe devolver un JSON con el nombre del servicio y los endpoints disponibles.

## Endpoints en detalle

### `POST /register`

**Petición**

```json
{
  "usuario": "Maria",
  "password": "claveSegura123"
}
```

**Respuesta `201 Created`**

```json
{
  "estado": "exitoso",
  "mensaje": "Usuario registrado correctamente"
}
```

**Respuesta `400 Bad Request`** si falta `usuario` o `password`.

### `POST /login`

**Petición**

```json
{
  "usuario": "Juan",
  "password": "juan123"
}
```

**Respuesta `200 OK`**

```json
{
  "estado": "exitoso",
  "mensaje": "Autenticación satisfactoria",
  "usuario": "Juan"
}
```

**Respuesta `401 Unauthorized`** si el usuario no existe o la contraseña es incorrecta. El
mensaje es el mismo en los dos casos, a propósito: no revela qué usuarios existen.

**Respuesta `400 Bad Request`** si falta algún campo.

## Códigos de estado

| Código | Cuándo |
|---|---|
| `200` | Autenticación correcta o información del servicio |
| `201` | Usuario registrado |
| `400` | Faltan campos en el cuerpo de la petición |
| `401` | Credenciales incorrectas |
| `500` | Error del servidor o de la base de datos |

## Prueba rápida

Con la extensión de REST de tu editor, o con `curl`:

```bash
curl -X POST http://localhost/auth_api_sena/login ^
  -H "Content-Type: application/json" ^
  -d "{\"usuario\":\"Juan\",\"password\":\"juan123\"}"
```

El repo incluye **`pruebas.html`**, una página para probar los dos endpoints desde el navegador
sin instalar nada: abrirla y enviar el formulario.

## Base de datos

Tabla `usuarios` de la base `auth_api`:

| Campo | Tipo | Notas |
|---|---|---|
| `id` | INT, AUTO_INCREMENT | Clave primaria |
| `usuario` | VARCHAR(50) | Índice **UNIQUE** |
| `password` | VARCHAR(255) | Hash bcrypt, nunca texto plano |
| `fecha_registro` | DATETIME | Valor por defecto: `CURRENT_TIMESTAMP` |

El volcado incluye un usuario de ejemplo:

| Usuario | Contraseña |
|---|---|
| `Juan` | `juan123` |

## Seguridad

| Medida | Estado |
|---|---|
| Contraseñas con `password_hash` / `password_verify` | ✅ Implementado |
| Consultas con PDO y parámetros vinculados | ✅ Implementado |
| Mensaje de error idéntico para usuario y contraseña incorrectos | ✅ Implementado |
| Códigos HTTP semánticos | ✅ Implementado |
| Límite de intentos en `/login` | ⚠️ Pendiente |
| Emisión de token (JWT o similar) | ⚠️ Fuera del alcance de la evidencia |
| Credenciales en variables de entorno | ⚠️ Pendiente (hoy van en `config/database.php`) |
| CORS restringido a un origen | ⚠️ Pendiente (hoy es `*`) |

## Estructura del proyecto

```
auth_api_sena/
├── index.php                     Router: lee la URI y llama al controlador
├── .htaccess                     Reescritura de todas las rutas a index.php
├── config/
│   └── database.php              Conexión PDO a MySQL
├── controllers/
│   └── AuthController.php        Registro y login, respuestas JSON
├── models/
│   └── User.php                  Consultas a la tabla usuarios
├── sql/
│   └── auth_api.sql              Esquema y datos de ejemplo
└── pruebas.html                  Página de prueba manual
```

El flujo de una petición: `.htaccess` → `index.php` → `AuthController` → `User` → MySQL.

## Autor

Joel Castro — Tecnólogo en Análisis y Desarrollo de Software (ADSO), SENA.
