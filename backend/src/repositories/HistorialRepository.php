<?php

namespace Backend\Repositories;

use Backend\Config\Database;
use Backend\Models\Historial;
use PDO;

/** CRUD (DML con sentencias preparadas) de `Historiales`. */
class HistorialRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConexion();
    }

    public function crear(array $datos): int
    {
        $sql = 'INSERT INTO Historiales (descrip_historial, id_usuario, id_visitante) VALUES (:descrip, :id_usuario, :id_visitante)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':descrip'      => $datos['descrip_historial'],
            ':id_usuario'   => $datos['id_usuario'] ?? null,
            ':id_visitante' => $datos['id_visitante'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /** @return array Filas con el nombre del usuario (join), para el panel de administración. */
    public function listarRecientes(int $limite = 100): array
    {
        $sql = 'SELECT h.*, u.nom_usuario
                FROM Historiales h
                LEFT JOIN Usuarios u ON u.id_usuario = h.id_usuario
                ORDER BY h.fecha_ingreso DESC
                LIMIT :limite';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** @return Historial[] */
    public function listarPorUsuario(int $idUsuario): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Historiales WHERE id_usuario = :id ORDER BY fecha_ingreso DESC');
        $stmt->execute([':id' => $idUsuario]);
        return array_map(fn (array $fila) => Historial::desdeFila($fila), $stmt->fetchAll());
    }
}
