<?php

namespace Backend\Repositories;

use Backend\Config\Database;
use Backend\Models\Turno;
use PDO;

class TurnoRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConexion();
    }

    /** @return Turno[] */
    public function listar(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM Turnos ORDER BY id_turno');
        return array_map(fn (array $fila) => Turno::desdeFila($fila), $stmt->fetchAll());
    }

    public function buscarPorId(int $idTurno): ?Turno
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Turnos WHERE id_turno = :id LIMIT 1');
        $stmt->execute([':id' => $idTurno]);
        $fila = $stmt->fetch();
        return $fila ? Turno::desdeFila($fila) : null;
    }

    public function crear(string $nombre, string $descripcion): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO Turnos (nom_turno, descrip_turno) VALUES (:nombre, :descripcion)');
        $stmt->execute([':nombre' => $nombre, ':descripcion' => $descripcion]);
        return (int) $this->pdo->lastInsertId();
    }
}
