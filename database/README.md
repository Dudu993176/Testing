# Scripts SQL

Importar en este orden (phpMyAdmin → Importar, o `mysql -u ... fadecode < archivo.sql`):

1. **01_DDL_tablas_e_indices.sql** — Crea las tablas, claves primarias/foráneas e índices.
2. **02_Datos_prueba.sql** — Carga datos de ejemplo (usuarios, ofertas, cursos, etc.).
3. **03_RNE_y_triggers.sql** — Ajustes de requerimientos (RFE-01 a RFE-07): aviso de cupo,
   autorización de menores, consentimiento de imagen, aviso de instalaciones
   compartidas del Polideportivo y validación de adjuntos PDF.
4. **04_mejoras_backend.sql** — Cambios necesarios para el backend PHP: tabla
   `Borradores` (guardado de progreso), columna `archivo_comprobante`, claves
   únicas en `Usuarios` (email/cédula) e índices de apoyo para los listados
   del panel de administración.
5. **05_correccion_historial_visitantes.sql** — Corrige un error real
   detectado en la autorevisión final: `Historiales.id_usuario` quedó como
   `NOT NULL` en el script 01, pero el registro de accesos anónimos
   (`Visitante::registrarAcceso()`) necesita guardarlo en `NULL` (sólo llena
   `id_visitante`), igual que ya permite `Sugerencias.id_usuario`. Sin este
   script, cualquier acceso de un visitante sin cuenta rompía con "Column
   'id_usuario' cannot be null". Ver sección "Errores detectados" del README
   de la raíz para el detalle completo.

Después de importar los 5 scripts, correr una vez:

```bash
php backend/scripts/rehash_seed_passwords.php
```

para convertir las contraseñas de `02_Datos_prueba.sql` (texto plano) a hash
bcrypt, que es el único formato que acepta el backend para cuentas nuevas.

Ver el README de la raíz del proyecto para las instrucciones completas de
instalación en XAMPP y Debian.
