<?php

namespace Backend\Repositories;

use Backend\Config\Database;
use Backend\Models\Materia;
use PDO;

class MateriaRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConexion();
    }

    /** @return Materia[] */
    public function listar(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM Materias ORDER BY nom_materia');
        return array_map(fn (array $fila) => Materia::desdeFila($fila), $stmt->fetchAll());
    }

    public function crear(string $nombre, string $descripcion): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO Materias (nom_materia, descrip_materia) VALUES (:nombre, :descripcion)');
        $stmt->execute([':nombre' => $nombre, ':descripcion' => $descripcion]);
        return (int) $this->pdo->lastInsertId();
    }
}
