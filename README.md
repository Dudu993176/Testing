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
│   ├── 04_mejoras_backend.sql
│   └── 05_correccion_historial_visitantes.sql          # Corrige un error real, ver sección 6
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
   5. `05_correccion_historial_visitantes.sql` (corrige un error real detectado
      en la revisión final, ver sección 6)
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
for f in 01_DDL_tablas_e_indices 02_Datos_prueba 03_RNE_y_triggers 04_mejoras_backend 05_correccion_historial_visitantes; do
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

## 5. Errores detectados

Se pidió explícitamente **no modificar ningún archivo del frontend original**
(`fadecode-dev`) al reorganizar el proyecto, así que los errores de esa parte
quedan documentados acá (con archivo y línea) en vez de corregidos en el
lugar. Los errores encontrados en el código *agregado* en este trabajo
(backend, base de datos, `.htaccess`) sí se corrigieron, porque no forman
parte de "el código original" que no había que tocar; también se listan.

### 5.1 Errores en el frontend original (sin corregir, tal como se descargó)

| # | Archivo(s) | Error | Por qué importa |
|---|---|---|---|
| 1 | `Anexo-BaltasarBrum.html:171`, `Anexo-TomasGomensoro.html:171` | `<script src="js/main.js">`: la carpeta `js/` no existe en el proyecto. El archivo real es `assets/javascript/main.js` (así lo usa correctamente la página hermana `Anexo-Polideportivo.html:295`). | El script nunca carga (404): cualquier comportamiento de `main.js` (menú, etc.) no funciona en esas dos páginas, en ningún sistema operativo. |
| 2 | 19 páginas HTML (`index.html`, `Login-in.html`, `Registrarse.html`, `Sugerencias.html`, `Noticias.html`, `Eventos.html`, `Normativas.html`, `UbicacionUTU.html`, `Cuenta.html`, `Error.html`, `Preinscribirse.html`, `Laboratorio.html`, `Actividades-Polideportivo.html`, `Figuras-Polideportivo.html`, `Anexo-*.html` y todo `OfertasEducativas/*.html`) | Referencian `Iconos/Icon White/Facebook.png` (con "F" mayúscula), pero el archivo real en el repo se llama `facebook.png` (minúscula). | **Funciona en Windows/XAMPP** (sistema de archivos insensible a mayúsculas) pero **rompe en Apache sobre Debian** (Linux es sensible a mayúsculas/minúsculas): el ícono de Facebook da 404 en producción. Es el mismo tipo de bug que ya se había corregido para `registrarse.js`/`Auth.css` en la reorganización, pero no se detectó en este ícono. |
| 3 | Las mismas 19 páginas del punto 2 | Referencian `Iconos/icon white/brand-whatsapp.PNG` (carpeta en minúscula), pero la carpeta real es `Iconos/Icon White/` (con mayúsculas). | Mismo problema que el punto 2: el ícono de WhatsApp del pie de página da 404 en Apache/Debian aunque se vea bien en XAMPP/Windows. |
| 4 | `OfertasEducativas/Menu-de-Ofertas.html:118`, `OfertasEducativas/construccion-muebles-por-diseno.html` | Referencian `../Iconos/Ofertas/MueblesDiseño.png`, pero el archivo dentro de `Iconos/Ofertas/` quedó guardado como `MueblesDise#U00f1o.png` (la "ñ" se corrompió a texto literal "#U00f1" al comprimirse/descomprimirse el .zip original). | La imagen de la oferta "Construcción - Muebles por Diseño" nunca carga, en ningún sistema operativo (el nombre de archivo real no coincide con ninguna variante de mayúsculas/minúsculas). |
| 5 | `Figuras-Polideportivo.html:70` | `<img src="Recursos Polideportivo/coordinador-ejecutivo.jpg" alt="Nombre pendiente">`: no existe ninguna carpeta `Recursos Polideportivo/` en el proyecto (sólo `Recursos UTU/`). | Imagen rota; el propio `alt="Nombre pendiente"` sugiere que era un placeholder que quedó sin completar en el diseño original. |

**Recomendación de arreglo** (no aplicada, para no tocar el frontend
original): renombrar los archivos de imagen a exactamente el nombre que
usa el HTML (o viceversa, corregir el HTML) y cambiar `js/main.js` por
`assets/javascript/main.js` en los dos anexos. Como referencia, ítems 2 y 3
son el mismo patrón que ya se había corregido en `Login-in.js`/`auth.css`
durante la reorganización — simplemente no se llegó a estos íconos.

### 5.2 Errores/inconsistencias detectadas en el diagrama UML (`docs/diagramas/UML.drawio`)

| # | Dónde | Error |
|---|---|---|
| 1 | Clase `Historial` | Modela `idUsuario: int` e `idVisitante: int` como si ambos fueran siempre obligatorios, pero el propio método `esAccesoAnonimo(): bool` sólo tiene sentido si uno de los dos puede faltar. El diagrama no indica esa opcionalidad/exclusión mutua — y esa ambigüedad es la causa raíz del error de base de datos corregido en la sección 5.3 (`Historiales.id_usuario` quedó `NOT NULL` en el script generado a partir del diagrama). |
| 2 | Clases `Historial` y `Sugerencia` | Ninguna de las dos tiene una línea de asociación dibujada hacia `Visitante`, a pesar de que ambas tienen un atributo `idVisitante` y un método pensado explícitamente para el caso anónimo (`esAccesoAnonimo()`, `esAnonima()`). Las únicas conexiones dibujadas son `Sugerencia` → (compartimento de métodos de `Usuario`) y `Noticia` → (compartimento de métodos de `Administrador`). Falta la relación con `Visitante` en el propio dibujo. |
| 3 | Conectores de `Sugerencia` y `Noticia` | Los conectores no terminan en el borde de la clase `Usuario`/`Administrador` sino **dentro del compartimento de métodos** (celda hija `...-4` de `Usuario` y `...-9` de `Administrador` en el XML), en vez de apuntar al contenedor de la clase. Es un defecto de dibujo/enganche del `.drawio` (las flechas se ven entrando a la mitad de la lista de métodos), no sólo un detalle estético: dificulta releer el diagrama para regenerar el modelo de datos. |
| 4 | Clase `Alumno` | Sólo declara `cursoActual: string`. No incluye los campos que exigen los requerimientos (`RFE.pdf`, `03_RNE_y_triggers.sql`): `esMenor`, `autorizacionAdulto` (RFE-03) y `consentimientoImagen` (RFE-04). El diagrama quedó desactualizado respecto a los propios documentos de requerimientos del proyecto. |
| 5 | Clases `Curso`, `Materia`, `Turno`, `Horario` | El diagrama las relaciona con líneas directas 1 a 1 (`Curso`→`OfertaEducativa`, `Curso`→`Materia`, `Turno`→`OfertaEducativa`, `Turno`→`Horario`), pero el modelo de datos real (`01_DDL_tablas_e_indices.sql`) necesita tablas intermedias muchos-a-muchos (`Contienen`, `Pertenecen`, `Incluyen`) que no aparecen como clases/asociaciones en el UML. El diagrama de clases no describe del todo el esquema relacional que finalmente hizo falta. |
| 6 | Conector `NivelAcceso` → `Historial` | No corresponde a ninguna relación real: `Historiales` no tiene columna `id_nivel` ni el modelo `Historial` referencia `NivelAcceso`. Parece un conector mal enganchado al mover cajas en drawio (posiblemente destinado a `Usuario` → `Historial`, que ya existe por otro lado). |
| 7 | Clase `FormularioInteres` | `cursoInteres` está modelado como `string` libre en vez de una referencia (`idOferta`/`idCurso`) a `OfertaEducativa`/`Curso`. Es coherente con cómo quedó la tabla real (`curso_lista_interes VARCHAR`), pero como diseño no está normalizado: nada impide guardar un curso que no existe, y no se puede hacer `JOIN` para saber cuántos interesados tiene cada oferta sin comparar strings. |

### 5.3 Autorevisión del backend/BD agregado en este trabajo (errores encontrados y corregidos)

Después de escribir el backend se releyó todo el código nuevo (se corrió
`php -l` sobre los ~50 archivos PHP y `node --check` sobre los `.js` — ninguno
tiene errores de sintaxis) y se contrastó cada tabla contra el repositorio y
el modelo que la usa. Esto encontró:

- **Bug real, corregido con `database/05_correccion_historial_visitantes.sql`**:
  `Historiales.id_usuario` se creó como `int(11) NOT NULL` en
  `01_DDL_tablas_e_indices.sql` (generado a partir del UML, ver ítem 5.2.1),
  pero `Backend\Models\Visitante::registrarAcceso()` necesita insertar un
  historial con `id_usuario = NULL` (sólo completa `id_visitante`) para
  visitantes anónimos, igual que ya podía hacer `Sugerencias.id_usuario`
  (ese sí era `NULL`able desde el script 01). Sin el script 05, cualquier
  acceso de un visitante sin cuenta rompía con el error de MySQL *"Column
  'id_usuario' cannot be null"*.
- **Documentación copiada por error, corregida**: `database/.htaccess` y
  `docs/.htaccess` tenían el mismo comentario de `backend/.htaccess`
  ("Esta carpeta contiene la lógica PHP (POO) del backend..."), que no
  describe lo que hay en esas dos carpetas (scripts SQL y documentación,
  respectivamente). Las reglas de Apache (`Require all denied`) sí eran
  correctas en los tres archivos; sólo se corrigió el texto del comentario.
- **Endpoint sin usar desde la interfaz (no es un bug, pero quedó
  incompleto)**: `GET /api/historial.php` existe y funciona, pero
  `admin/panel.html`/`admin/admin.js` no tienen ninguna vista que lo
  consuma — el administrador no tiene forma de ver la bitácora de accesos
  desde el panel todavía. Se deja anotado en "Próximos pasos" (sección 7).
- **Verificado sin errores**: las sentencias SQL de los 14 repositorios usan
  parámetros con nombre (`PDO::prepare` + `execute([':param' => ...])`) en
  el 100% de los casos — no se encontró ninguna concatenación de datos de
  usuario dentro de una consulta. `Usuario::toArray()` nunca serializa
  `pass_usuario` (el hash no se filtra en ninguna respuesta JSON). Las
  contraseñas de prueba en texto plano de `02_Datos_prueba.sql` se
  documentan explícitamente como tales (sección 3.3) y el backend las
  compara sólo hasta que se corre `rehash_seed_passwords.php`.

---

## 6. Próximos pasos sugeridos

- Agregar al panel de administración una vista que consuma
  `GET /api/historial.php` (el endpoint ya existe; ver sección 5.3).
- Sumar CRUD de administración para `Cursos`, `Materias`, `Turnos` y
  `Horarios` (los repositorios ya existen en `backend/src/repositories/`;
  falta sólo el controlador en `api/` y la vista en `admin/panel.html`).
- Reemplazar las páginas estáticas de `OfertasEducativas/*.html` por una
  única plantilla que consuma `GET /api/ofertas.php`.
- Migrar los secretos (`DB_PASS`, claves de reCAPTCHA) a variables de
  entorno del servidor en vez de valores por defecto en `config.php`.
- Corregir los errores del frontend original listados en la sección 5.1
  (rutas de imágenes/script con mayúsculas/minúsculas distintas y el nombre
  de archivo corrupto de `MueblesDiseño.png`), respetando que no se tocó
  ese código en este trabajo a pedido explícito.
