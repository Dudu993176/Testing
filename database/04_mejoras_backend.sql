-- ============================================================================
-- 04_mejoras_backend.sql
-- Ajustes necesarios para conectar el backend PHP (POO + PDO) con el modelo
-- de datos original (01_DDL_tablas_e_indices.sql) y con las reglas de negocio
-- añadidas en 03_RNE_y_triggers.sql.
--
-- Ejecutar en este orden, DESPUÉS de 01, 02 y 03.
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 1) Borradores: soporta el requerimiento de "Guardado de progreso" del panel
--    de administración (formularios largos de Ofertas Educativas y Noticias).
--    Cada usuario tiene, como máximo, un borrador por tipo de formulario.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Borradores` (
  `id_borrador` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `formulario` varchar(60) NOT NULL COMMENT 'Identificador del formulario: oferta_educativa, noticia, etc.',
  `contenido_json` text NOT NULL COMMENT 'Estado del formulario serializado en JSON',
  `actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_borrador`),
  UNIQUE KEY `uq_borrador_usuario_formulario` (`id_usuario`, `formulario`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

ALTER TABLE `Borradores`
  ADD CONSTRAINT `Borradores_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id_usuario`) ON DELETE CASCADE;

-- ----------------------------------------------------------------------------
-- 1.b) Formularios_interes: guarda la ruta del comprobante adjunto opcional
--      (RFE del formulario de Preinscribirse.html). El archivo en sí se
--      guarda fuera del webroot, en backend/storage/comprobantes/.
-- ----------------------------------------------------------------------------
ALTER TABLE `Formularios_interes`
  ADD COLUMN IF NOT EXISTS `archivo_comprobante` VARCHAR(255) DEFAULT NULL;

-- ----------------------------------------------------------------------------
-- 2) Usuarios.email debe ser único: el login/registro del backend depende de
--    poder detectar correos (y cédulas) duplicados con una consulta preparada
--    en vez de recorrer toda la tabla.
-- ----------------------------------------------------------------------------
ALTER TABLE `Usuarios`
  ADD UNIQUE KEY IF NOT EXISTS `uq_usuarios_email` (`email`),
  ADD UNIQUE KEY IF NOT EXISTS `uq_usuarios_cedula` (`cedula`);

-- ----------------------------------------------------------------------------
-- 3) pass_usuario pasa a almacenar hashes bcrypt (password_hash de PHP), que
--    ocupan 60 caracteres con el algoritmo por defecto. Se deja documentado
--    por si en algún momento se migra a argon2id (requeriría ampliar a 255).
-- ----------------------------------------------------------------------------
-- ALTER TABLE `Usuarios` MODIFY `pass_usuario` VARCHAR(255) NOT NULL;

-- ----------------------------------------------------------------------------
-- 4) Índice de apoyo para listar sugerencias e historial por fecha, que es el
--    orden que usa el panel de administración.
-- ----------------------------------------------------------------------------
ALTER TABLE `Sugerencias` ADD INDEX IF NOT EXISTS `idx_sugerencias_fecha` (`fecha_sugerencia`);
ALTER TABLE `Historiales` ADD INDEX IF NOT EXISTS `idx_historiales_fecha` (`fecha_ingreso`);
ALTER TABLE `Formularios_interes` ADD INDEX IF NOT EXISTS `idx_formularios_fecha` (`fecha_form`);

-- ----------------------------------------------------------------------------
-- NOTA IMPORTANTE sobre 02_Datos_prueba.sql:
-- Esos INSERT usan contraseñas en texto plano ('abcd1234') únicamente para
-- poder poblar la base rápido en un entorno de práctica. El backend real
-- (backend/src/services/AuthService.php) sólo genera y verifica contraseñas
-- con password_hash()/password_verify() (bcrypt). Después de importar los
-- datos de prueba, ejecutar UNA vez:
--
--   php backend/scripts/rehash_seed_passwords.php
--
-- para convertir esas contraseñas de ejemplo a hashes válidos sin tener que
-- volver a registrarse manualmente.
