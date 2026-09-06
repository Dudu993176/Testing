<?php

namespace Backend\Repositories;

use Backend\Config\Database;
use Backend\Models\Horario;
use PDO;

class HorarioRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConexion();
    }

    /** @return Horario[] */
    public function listar(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM Horarios ORDER BY id_horario');
        return array_map(fn (array $fila) => Horario::desdeFila($fila), $stmt->fetchAll());
    }

    public function crear(Horario $horario): int
    {
        $sql = 'INSERT INTO Horarios (`día_semana`, hora_inicio, hora_fin) VALUES (:dia, :inicio, :fin)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':dia'    => $horario->getDiaSemana(),
            ':inicio' => $horario->getHoraInicio(),
            ':fin'    => $horario->getHoraFin(),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /** @return Horario[] Horarios asociados a un turno vía la tabla `Incluyen`. */
    public function listarPorTurno(int $idTurno): array
    {
        $sql = 'SELECT h.* FROM Horarios h
                INNER JOIN Incluyen i ON i.id_horario = h.id_horario
                WHERE i.id_turno = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $idTurno]);
        return array_map(fn (array $fila) => Horario::desdeFila($fila), $stmt->fetchAll());
    }
}
