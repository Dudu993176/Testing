<?php

namespace Backend\Repositories;

use Backend\Config\Database;
use Backend\Models\Alumno;
use PDO;

class AlumnoRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConexion();
    }

    private const SELECT_BASE = 'SELECT u.*, al.curso_actual, al.es_menor, al.autorizacion_adulto, al.consentimiento_imagen
        FROM Usuarios u
        INNER JOIN Alumnos al ON al.id_usuario = u.id_usuario';

    public function crear(int $idUsuario, array $datos = []): bool
    {
        $sql = 'INSERT INTO Alumnos (id_usuario, curso_actual, es_menor, autorizacion_adulto, consentimiento_imagen)
                VALUES (:id, :curso, :es_menor, :autorizacion, :consentimiento)';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id'             => $idUsuario,
            ':curso'          => $datos['curso_actual'] ?? null,
            ':es_menor'       => !empty($datos['es_menor']) ? 1 : 0,
            ':autorizacion'   => !empty($datos['autorizacion_adulto']) ? 1 : 0,
            ':consentimiento' => !empty($datos['consentimiento_imagen']) ? 1 : 0,
        ]);
    }

    public function buscarPorId(int $idUsuario): ?Alumno
    {
        $stmt = $this->pdo->prepare(self::SELECT_BASE . ' WHERE u.id_usuario = :id LIMIT 1');
        $stmt->execute([':id' => $idUsuario]);
        $fila = $stmt->fetch();
        return $fila ? Alumno::desdeFila($fila) : null;
    }

    /** @return Alumno[] */
    public function listar(): array
    {
        $stmt = $this->pdo->query(self::SELECT_BASE . ' ORDER BY u.fecha_registro DESC');
        return array_map(fn (array $fila) => Alumno::desdeFila($fila), $stmt->fetchAll());
    }

    public function actualizarCurso(int $idUsuario, string $curso): bool
    {
        $stmt = $this->pdo->prepare('UPDATE Alumnos SET curso_actual = :curso WHERE id_usuario = :id');
        return $stmt->execute([':curso' => $curso, ':id' => $idUsuario]);
    }
}
