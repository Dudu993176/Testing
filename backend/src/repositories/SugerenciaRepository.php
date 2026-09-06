<?php

namespace Backend\Repositories;

use Backend\Config\Database;
use Backend\Models\Sugerencia;
use PDO;

/** CRUD (DML con sentencias preparadas) de `Sugerencias`. */
class SugerenciaRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConexion();
    }

    public function buscarPorId(int $idSugerencia): ?Sugerencia
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Sugerencias WHERE id_sugerencia = :id LIMIT 1');
        $stmt->execute([':id' => $idSugerencia]);
        $fila = $stmt->fetch();
        return $fila ? Sugerencia::desdeFila($fila) : null;
    }

    /** @return array Filas con el nombre del autor (join), para moderación en el panel. */
    public function listarConAutor(int $limite = 100): array
    {
        $sql = 'SELECT s.*, u.nom_usuario
                FROM Sugerencias s
                LEFT JOIN Usuarios u ON u.id_usuario = s.id_usuario
                ORDER BY s.fecha_sugerencia DESC
                LIMIT :limite';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function crear(array $datos): int
    {
        $sql = 'INSERT INTO Sugerencias (contenido, id_usuario, id_visitante) VALUES (:contenido, :id_usuario, :id_visitante)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':contenido'    => $datos['contenido'],
            ':id_usuario'   => $datos['id_usuario'] ?? null,
            ':id_visitante' => $datos['id_visitante'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function eliminar(int $idSugerencia): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM Sugerencias WHERE id_sugerencia = :id');
        return $stmt->execute([':id' => $idSugerencia]);
    }

    /** Cuenta cuántas sugerencias envió la misma IP anonimizada en los últimos $minutos (anti-spam simple). */
    public function contarRecientesDeVisitante(int $idVisitante, int $minutos = 5): int
    {
        $sql = 'SELECT COUNT(*) FROM Sugerencias
                WHERE id_visitante = :id_visitante
                AND fecha_sugerencia >= (NOW() - INTERVAL :minutos MINUTE)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_visitante', $idVisitante, PDO::PARAM_INT);
        $stmt->bindValue(':minutos', $minutos, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
