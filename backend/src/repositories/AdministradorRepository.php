<?php

namespace Backend\Repositories;

use Backend\Config\Database;
use Backend\Models\Administrador;
use PDO;

class AdministradorRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConexion();
    }

    private const SELECT_BASE = 'SELECT u.*, a.cargo
        FROM Usuarios u
        INNER JOIN Administradores a ON a.id_usuario = u.id_usuario';

    public function crear(int $idUsuario, string $cargo): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO Administradores (id_usuario, cargo) VALUES (:id, :cargo)');
        return $stmt->execute([':id' => $idUsuario, ':cargo' => $cargo]);
    }

    public function buscarPorId(int $idUsuario): ?Administrador
    {
        $stmt = $this->pdo->prepare(self::SELECT_BASE . ' WHERE u.id_usuario = :id LIMIT 1');
        $stmt->execute([':id' => $idUsuario]);
        $fila = $stmt->fetch();
        return $fila ? Administrador::desdeFila($fila) : null;
    }

    /** @return Administrador[] */
    public function listar(): array
    {
        $stmt = $this->pdo->query(self::SELECT_BASE . ' ORDER BY u.fecha_registro DESC');
        return array_map(fn (array $fila) => Administrador::desdeFila($fila), $stmt->fetchAll());
    }

    public function actualizarCargo(int $idUsuario, string $cargo): bool
    {
        $stmt = $this->pdo->prepare('UPDATE Administradores SET cargo = :cargo WHERE id_usuario = :id');
        return $stmt->execute([':cargo' => $cargo, ':id' => $idUsuario]);
    }
}
