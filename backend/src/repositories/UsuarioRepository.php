<?php

namespace Backend\Repositories;

use Backend\Config\Database;
use Backend\Models\Usuario;
use PDO;

/**
 * Acceso a datos (DML) de la tabla `Usuarios`.
 *
 * Todas las consultas usan sentencias preparadas (PDO::prepare + parámetros
 * con nombre) para evitar inyección SQL: nunca se concatena texto del
 * usuario dentro del SQL.
 */
class UsuarioRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConexion();
    }

    public function buscarPorId(int $idUsuario): ?Usuario
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Usuarios WHERE id_usuario = :id LIMIT 1');
        $stmt->execute([':id' => $idUsuario]);
        $fila = $stmt->fetch();
        return $fila ? Usuario::desdeFila($fila) : null;
    }

    public function buscarPorCedula(int $cedula): ?Usuario
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Usuarios WHERE cedula = :cedula LIMIT 1');
        $stmt->execute([':cedula' => $cedula]);
        $fila = $stmt->fetch();
        return $fila ? Usuario::desdeFila($fila) : null;
    }

    public function buscarPorEmail(string $email): ?Usuario
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Usuarios WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $fila = $stmt->fetch();
        return $fila ? Usuario::desdeFila($fila) : null;
    }

    public function existeCedula(int $cedula): bool
    {
        return $this->buscarPorCedula($cedula) !== null;
    }

    public function existeEmail(string $email): bool
    {
        return $this->buscarPorEmail($email) !== null;
    }

    /** Devuelve el "rol" (administrador/alumno) de un usuario, o null si no tiene subtipo asignado. */
    public function obtenerRol(int $idUsuario): ?string
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM Administradores WHERE id_usuario = :id');
        $stmt->execute([':id' => $idUsuario]);
        if ($stmt->fetchColumn()) {
            return 'administrador';
        }

        $stmt = $this->pdo->prepare('SELECT 1 FROM Alumnos WHERE id_usuario = :id');
        $stmt->execute([':id' => $idUsuario]);
        if ($stmt->fetchColumn()) {
            return 'alumno';
        }

        return null;
    }

    /**
     * INSERT en `Usuarios`. Devuelve el id_usuario autogenerado (0 si falla).
     * $datos debe traer: cedula, nom_usuario, email, pass_usuario (ya
     * hasheada), pais, id_nivel.
     */
    public function crear(array $datos): int
    {
        $sql = 'INSERT INTO Usuarios (cedula, nom_usuario, email, pass_usuario, pais, id_nivel)
                VALUES (:cedula, :nom_usuario, :email, :pass_usuario, :pais, :id_nivel)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':cedula'       => $datos['cedula'],
            ':nom_usuario'  => $datos['nom_usuario'],
            ':email'        => $datos['email'],
            ':pass_usuario' => $datos['pass_usuario'],
            ':pais'         => $datos['pais'],
            ':id_nivel'     => $datos['id_nivel'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function actualizarPerfil(int $idUsuario, array $datos): bool
    {
        $sql = 'UPDATE Usuarios SET nom_usuario = :nom_usuario, email = :email, pais = :pais WHERE id_usuario = :id';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nom_usuario' => $datos['nom_usuario'],
            ':email'       => $datos['email'],
            ':pais'        => $datos['pais'],
            ':id'          => $idUsuario,
        ]);
    }

    public function actualizarPassword(int $idUsuario, string $passHash): bool
    {
        $stmt = $this->pdo->prepare('UPDATE Usuarios SET pass_usuario = :pass WHERE id_usuario = :id');
        return $stmt->execute([':pass' => $passHash, ':id' => $idUsuario]);
    }

    public function eliminar(int $idUsuario): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM Usuarios WHERE id_usuario = :id');
        return $stmt->execute([':id' => $idUsuario]);
    }
}
