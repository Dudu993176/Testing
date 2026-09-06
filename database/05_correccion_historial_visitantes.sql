-- ============================================================================
-- 05_correccion_historial_visitantes.sql
--
-- Corrige un error real encontrado en la autorevisión final del backend
-- (ver sección "Errores detectados" del README de la raíz):
--
--   `Historiales.id_usuario` quedó definido en 01_DDL_tablas_e_indices.sql
--   como `int(11) NOT NULL`, pero `Backend\Models\Visitante::registrarAcceso()`
--   (y, en general, cualquier acceso anónimo) inserta una fila con
--   `id_usuario = NULL` y sólo `id_visitante` completo -- exactamente el
--   mismo patrón que ya soporta `Sugerencias.id_usuario`, que sí es NULLABLE.
--   Con la restricción original, esa inserción fallaba con el error de MySQL
--   "Column 'id_usuario' cannot be null" apenas un visitante sin cuenta
--   generaba un evento de historial.
--
-- Ejecutar DESPUÉS de 01, 02, 03 y 04.
-- ============================================================================

ALTER TABLE `Historiales`
  MODIFY `id_usuario` int(11) DEFAULT NULL;
