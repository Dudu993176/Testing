<?php

namespace Backend\Repositories;

use Backend\Config\Database;
use Backend\Models\Curso;
use PDO;

class CursoRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConexion();
    }

    /** @return Curso[] */
    public function listar(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM Cursos ORDER BY nom_curso');
        return array_map(fn (array $fila) => Curso::desdeFila($fila), $stmt->fetchAll());
    }

    public function buscarPorId(int $idCurso): ?Curso
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Cursos WHERE id_curso = :id LIMIT 1');
        $stmt->execute([':id' => $idCurso]);
        $fila = $stmt->fetch();
        return $fila ? Curso::desdeFila($fila) : null;
    }

    public function crear(string $nombre, string $descripcion): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO Cursos (nom_curso, descrip) VALUES (:nombre, :descripcion)');
        $stmt->execute([':nombre' => $nombre, ':descripcion' => $descripcion]);
        return (int) $this->pdo->lastInsertId();
    }

    /** @return \Backend\Models\Materia[] Materias asociadas vía la tabla `Pertenecen`. */
    public function listarMateriasAsociadas(int $idCurso): array
    {
        $sql = 'SELECT m.* FROM Materias m
                INNER JOIN Pertenecen p ON p.id_materia = m.id_materia
                WHERE p.id_curso = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $idCurso]);
        return array_map(fn (array $fila) => \Backend\Models\Materia::desdeFila($fila), $stmt->fetchAll());
    }

    public function agregarMateria(int $idCurso, int $idMateria): bool
    {
        $stmt = $this->pdo->prepare('INSERT IGNORE INTO Pertenecen (id_curso, id_materia) VALUES (:curso, :materia)');
        return $stmt->execute([':curso' => $idCurso, ':materia' => $idMateria]);
    }
}
