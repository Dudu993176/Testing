<?php

namespace Backend\Repositories;

use Backend\Config\Database;
use PDO;

/**
 * Soporta el requerimiento de "Guardado de progreso": permite que el panel
 * de administración autoguarde en el servidor lo que un admin va tipeando
 * en un formulario largo (Oferta Educativa, Noticia) y lo recupere si
 * cierra la pestaña sin terminar de enviarlo.
 *
 * Tabla: `Borradores` (ver database/04_mejoras_backend.sql).
 */
class BorradorRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConexion();
    }

    public function obtener(int $idUsuario, string $formulario): ?array
    {
        $sql = 'SELECT contenido_json, actualizado_en FROM Borradores WHERE id_usuario = :id AND formulario = :form LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $idUsuario, ':form' => $formulario]);
        $fila = $stmt->fetch();
        if (!$fila) {
            return null;
        }
        return [
            'contenido'      => json_decode($fila['contenido_json'], true) ?? [],
            'actualizado_en' => $fila['actualizado_en'],
        ];
    }

    /** INSERT ... ON DUPLICATE KEY UPDATE: crea o reemplaza el único borrador por usuario+formulario. */
    public function guardar(int $idUsuario, string $formulario, array $contenido): bool
    {
        $sql = 'INSERT INTO Borradores (id_usuario, formulario, contenido_json)
                VALUES (:id, :form, :contenido)
                ON DUPLICATE KEY UPDATE contenido_json = VALUES(contenido_json)';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id'        => $idUsuario,
            ':form'      => $formulario,
            ':contenido' => json_encode($contenido, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function eliminar(int $idUsuario, string $formulario): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM Borradores WHERE id_usuario = :id AND formulario = :form');
        return $stmt->execute([':id' => $idUsuario, ':form' => $formulario]);
    }
}
