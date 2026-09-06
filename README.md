# FadeCode — Sitio institucional + Backend (Escuela Técnica N.º 1, Bella Unión)

Sitio web institucional de la Escuela Técnica 1 "Mtro. Téc. Sergio González
Olaizola" (UTU, Bella Unión) con backend propio en **PHP orientado a
objetos** y base de datos **MySQL/MariaDB**, pensado para correr en
**XAMPP** (desarrollo/Windows) o **Apache + MySQL sobre Debian**
(producción), administrable desde **phpMyAdmin**.

Este repositorio reorganiza y completa el proyecto descargado del repo de
desarrollo (`fadecode-dev`): se mantiene TODO el frontend original (HTML,
CSS, imágenes) y se agrega el backend que faltaba, siguiendo el diagrama de
clases (`docs/diagramas/UML.drawio`) y el modelo de datos
(`database/01_DDL_tablas_e_indices.sql`).

---

## 1. Estructura del proyecto

```
.
├── index.html, Login-in.html, Registrarse.html, ...   # Páginas del sitio (sin cambios de ubicación)
├── OfertasEducativas/                                  # Sub-sitio de ofertas educativas (HTML estático)
├── admin/                                              # Panel de administración (SPA en HTML/CSS/JS)
│   ├── panel.html
│   ├── admin.css
│   └── admin.js
├── assets/
│   ├── css/                                            # Hojas de estilo del sitio público
│   └── javascript/                                     # JS del sitio público (Fetch/Async-Await + validación)
├── Iconos/, Imagenes_UTU/, Recursos UTU/, materiales/  # Recursos estáticos
│
├── api/                                                 # Endpoints REST (controladores finos, PHP)
│   ├── bootstrap.php                                   # Autoload + sesión + manejo de errores en JSON
│   ├── auth/ (registro.php, login.php, logout.php, sesion.php)
│   ├── alumnos.php, administradores.php                # Listados para el panel ("Ver Usuarios")
│   ├── ofertas.php, noticias.php                       # CRUD (Ofertas Educativas / Noticias)
│   ├── sugerencias.php, formularios-interes.php        # Sugerencias y Preinscripciones
│   ├── historial.php                                   # Bitácora de accesos (sólo admin)
│   └── borradores.php                                  # "Guardado de progreso" del panel admin
│
├── backend/                                             # Lógica PHP orientada a objetos (NO accesible por HTTP)
│   ├── autoload.php                                    # Autoloader PSR-4 propio (namespace Backend\)
│   ├── src/
│   │   ├── config/      (config.php, Database.php)     # Conexión PDO (Singleton) + configuración
│   │   ├── core/        (Response, Validator, Session) # Utilidades transversales
│   │   ├── models/      (Usuario, Administrador, Alumno, Visitante, NivelAcceso,
│   │   │                 OfertaEducativa, Noticia, FormularioInteres, Historial,
│   │   │                 Sugerencia, Turno, Curso, Materia, Horario)
│   │   ├── repositories/(un Repository por entidad: DML con sentencias preparadas)
│   │   └── services/    (casos de uso: valida + orquesta repos, un Service por función)
│   ├── scripts/rehash_seed_passwords.php               # Ver sección 4
│   └── storage/comprobantes/                           # Archivos subidos en Preinscribirse.html
│
├── database/                                            # Scripts SQL (importar en este orden)
│   ├── 01_DDL_tablas_e_indices.sql
│   ├── 02_Datos_prueba.sql
│   ├── 03_RNE_y_triggers.sql
│   └── 04_mejoras_backend.sql
│
└── docs/                                                # UML, diagramas ER, documentos de requerimientos (Word/PDF)
    ├── diagramas/ (UML.drawio, UML.drawio.png, DiagramaMer/)
    └── requisitos/ (RFE.pdf, Documento BD.pdf, entregas .docx, Gantt, etc.)
```

`api/` y `backend/` quedan como carpetas hermanas de las páginas HTML: todo
el proyecto se copia tal cual a `htdocs/` (XAMPP) o `/var/www/html/`
(Debian), sin tener que cambiar el `DocumentRoot`. `backend/` tiene su
propio `.htaccess` (`Require all denied`) para que nadie pueda pedirlo por
URL: sólo lo usan los scripts de `api/` con `require` del lado del servidor.

---

## 2. Stack utilizado

| Capa | Tecnología |
|---|---|
| Maquetado / estilos | HTML5 + CSS3 (+ Bootstrap 5 por CDN) |
| Interactividad y validación de formularios | JavaScript (vanilla, `fetch` + `async/await`) |
| Backend | PHP 8 orientado a objetos (namespaces, clases, sin frameworks) |
| Acceso a datos | PDO + sentencias preparadas (prevención de inyección SQL) |
| Base de datos | MySQL / MariaDB |
| Administración de BD | phpMyAdmin |
| Servidor de desarrollo | XAMPP (Apache + MySQL + PHP) |
| Servidor de producción | Debian + Apache2 + MySQL/MariaDB + PHP |

---

## 3. Instalación

### 3.1 Con XAMPP (Windows/Linux/Mac) — desarrollo

1. Instalar [XAMPP](https://www.apachefriends.org/) y arrancar **Apache** y **MySQL** desde el panel de control.
2. Copiar toda esta carpeta del proyecto dentro de `htdocs/`, por ejemplo:
   `C:\xampp\htdocs\fadecode\` (Windows) o `/opt/lampp/htdocs/fadecode/` (Linux).
3. Abrir **phpMyAdmin** (`http://localhost/phpmyadmin`) y crear una base de datos
   llamada `fadecode` (cotejamiento `utf8mb4_unicode_ci` o similar).
4. Importar, **en este orden**, los archivos de `database/` (pestaña "Importar"
   de phpMyAdmin, o pegándolos en la pestaña SQL):
   1. `01_DDL_tablas_e_indices.sql`
   2. `02_Datos_prueba.sql`
   3. `03_RNE_y_triggers.sql`
   4. `04_mejoras_backend.sql`
5. Por defecto, `backend/src/config/config.php` ya apunta a los valores típicos
   de XAMPP (`host=127.0.0.1`, usuario `root`, sin contraseña, base `fadecode`).
   Si tu XAMPP usa otro usuario/contraseña, no hace falta editar el archivo:
   basta con definir las variables de entorno `DB_HOST`, `DB_NAME`, `DB_USER`,
   `DB_PASS` (por ejemplo, con `SetEnv` en la configuración de Apache).
6. Ejecutar una vez el script que convierte las contraseñas de prueba a hash
   (ver sección 4) y entrar a `http://localhost/fadecode/index.html`.

### 3.2 Con Apache + MySQL en Debian — producción

```bash
sudo apt update
sudo apt install apache2 mysql-server php php-mysql libapache2-mod-php phpmyadmin

# Copiar el proyecto
sudo cp -r fadecode /var/www/html/
sudo chown -R www-data:www-data /var/www/html/fadecode

# Crear la base de datos y un usuario dedicado (evitar usar "root" en la app)
sudo mysql -u root -p <<'SQL'
CREATE DATABASE fadecode CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'fadecode_app'@'localhost' IDENTIFIED BY 'una-clave-fuerte-aqui';
GRANT SELECT, INSERT, UPDATE, DELETE ON fadecode.* TO 'fadecode_app'@'localhost';
FLUSH PRIVILEGES;
SQL

# Importar los scripts SQL en orden
for f in 01_DDL_tablas_e_indices 02_Datos_prueba 03_RNE_y_triggers 04_mejoras_backend; do
  mysql -u fadecode_app -p fadecode < database/$f.sql
done
```

Definir las variables de entorno del sitio (por ejemplo, en el
`<VirtualHost>` de Apache):

```apache
<VirtualHost *:80>
    DocumentRoot /var/www/html/fadecode
    SetEnv APP_ENV production
    SetEnv DB_HOST 127.0.0.1
    SetEnv DB_NAME fadecode
    SetEnv DB_USER fadecode_app
    SetEnv DB_PASS una-clave-fuerte-aqui
    <Directory /var/www/html/fadecode>
        AllowOverride All
    </Directory>
</VirtualHost>
```

`AllowOverride All` es necesario para que el `.htaccess` de `backend/`
(que bloquea el acceso directo por HTTP a la carpeta) tenga efecto.

### 3.3 Contraseñas de la base de datos de prueba

`database/02_Datos_prueba.sql` carga usuarios con contraseñas en **texto
plano** (`abcd1234`), sólo para tener datos de ejemplo rápido. El backend
real siempre usa `password_hash()`/`password_verify()` (bcrypt). Después de
importar los 4 scripts SQL, ejecutar **una vez**:

```bash
php backend/scripts/rehash_seed_passwords.php
```

Esto convierte esas contraseñas de ejemplo a hash bcrypt sin cambiarlas
(seguís iniciando sesión con `abcd1234`, sólo cambia cómo se guardan).

Usuarios de prueba (cédula / contraseña) después de correr el script:

| Cédula | Contraseña | Rol |
|---|---|---|
| 12412414 | abcd1234 | Administrador |
| 987421 | abcd1234 | Administrador |
| 123456 | abcd1234 | Alumno |
| 32152335 | abcd1234 | (visitante, sin login: tabla `Visitantes`) |

---

## 4. Arquitectura del backend (POO)

El backend sigue una separación clásica en capas, mapeando 1 a 1 con las
clases del diagrama UML (`docs/diagramas/UML.drawio`):

```
api/*.php  (controladores finos, uno por recurso HTTP)
    │  reciben la petición, llaman al Service correspondiente
    ▼
backend/src/services/*.php   (casos de uso: validan y orquestan)
    │  Validator (dual: repite del lado del servidor lo que ya validó el JS)
    ▼
backend/src/repositories/*.php  (una clase por tabla: SELECT/INSERT/UPDATE/DELETE
    │                             con PDO y sentencias preparadas)
    ▼
backend/src/models/*.php   (entidades del UML: Usuario, Administrador, Alumno,
                             Visitante, NivelAcceso, OfertaEducativa, Noticia,
                             FormularioInteres, Historial, Sugerencia, Turno,
                             Curso, Materia, Horario — con sus getters/setters
                             y métodos de negocio, ej. Usuario::autenticar())
```

### Funcionalidades pedidas, y dónde están implementadas

- **Validación de formularios (dual)**: cada formulario público
  (`Registrarse.html`, `Login-in.html`, `Sugerencias.html`,
  `Preinscribirse.html`) valida en `assets/javascript/*.js` y el backend
  vuelve a validar todo en `backend/src/services/*Service.php` con
  `backend/src/core/Validator.php`.
- **Mensajes de error contextualizados**: el backend responde
  `{ success:false, errors: { nombre_campo: "mensaje" } }`
  (`backend/src/core/Response.php`), y `assets/javascript/api-client.js`
  pinta cada mensaje junto a su campo (mismo patrón que ya usaba el JS
  original: `id="campo-error"`).
- **Comunicación con el servidor (Fetch/Async-Await)**: todo el frontend usa
  `assets/javascript/api-client.js` (fetch + async/await) en vez de
  `console.log`/`alert()`.
- **Guardado de progreso**: el formulario de "Ofertas Educativas (BD)" del
  panel de administración autoguarda en el servidor (`api/borradores.php` +
  tabla `Borradores`) cada 3 segundos de inactividad, y ofrece recuperarlo
  si se cierra la pestaña sin enviarlo.
- **Conexión a base de datos con DML (CRUD + sentencias preparadas)**: cada
  `backend/src/repositories/*.php` usa `PDO::prepare()` con parámetros con
  nombre; nunca se concatena texto del usuario dentro de una consulta SQL.

### Endpoints principales

| Método | Ruta | Descripción | Acceso |
|---|---|---|---|
| POST | `/api/auth/registro.php` | Crea una cuenta (Alumno) | Público |
| POST | `/api/auth/login.php` | Inicia sesión | Público |
| POST | `/api/auth/logout.php` | Cierra sesión | Autenticado |
| GET | `/api/auth/sesion.php` | Usuario actual (o null) | Público |
| GET | `/api/alumnos.php` | Lista alumnos | Administrador |
| GET | `/api/administradores.php` | Lista administradores | Administrador |
| GET | `/api/ofertas.php` | Ofertas vigentes (`?todas=1` para admin) | Público / Admin |
| POST/PUT/PATCH/DELETE | `/api/ofertas.php` | CRUD de ofertas | Administrador |
| GET/POST/PUT/DELETE | `/api/noticias.php` | CRUD de noticias | Público (GET) / Admin |
| POST | `/api/sugerencias.php` | Envía una sugerencia | Público (alumno o visitante anónimo) |
| GET/DELETE | `/api/sugerencias.php` | Modera sugerencias | Administrador |
| POST | `/api/formularios-interes.php` | Preinscripción (`multipart/form-data`) | Público |
| GET/DELETE | `/api/formularios-interes.php` | Lista/borra preinscripciones | Administrador |
| GET | `/api/historial.php` | Bitácora de accesos | Administrador |
| GET/PUT/DELETE | `/api/borradores.php?formulario=...` | Guardado de progreso | Administrador |

---

## 5. Próximos pasos sugeridos

- Sumar CRUD de administración para `Cursos`, `Materias`, `Turnos` y
  `Horarios` (los repositorios ya existen en `backend/src/repositories/`;
  falta sólo el controlador en `api/` y la vista en `admin/panel.html`).
- Reemplazar las páginas estáticas de `OfertasEducativas/*.html` por una
  única plantilla que consuma `GET /api/ofertas.php`.
- Migrar los secretos (`DB_PASS`, claves de reCAPTCHA) a variables de
  entorno del servidor en vez de valores por defecto en `config.php`.
