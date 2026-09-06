<?php

namespace Backend\Models;

/**
 * Clase Usuario (ver docs/diagramas/UML.drawio).
 *
 * Representa una fila de la tabla `Usuarios`. Administrador y Alumno la
 * extienden, igual que en el diagrama de clases y en el modelo de datos
 * (donde `Administradores`/`Alumnos` sólo agregan una columna extra sobre
 * `id_usuario`).
 */
class Usuario
{
    protected int $idUsuario = 0;
    protected int $cedula;
    protected string $nomUsuario;
    protected string $email;
    protected string $passUsuario; // hash bcrypt (password_hash), nunca texto plano
    protected string $pais;
    protected ?string $fechaRegistro = null;
    protected int $idNivel;

    public function __construct(
        int $cedula,
        string $nomUsuario,
        string $email,
        string $passUsuario,
        string $pais,
        int $idNivel
    ) {
        $this->cedula = $cedula;
        $this->nomUsuario = $nomUsuario;
        $this->email = $email;
        $this->passUsuario = $passUsuario;
        $this->pais = $pais;
        $this->idNivel = $idNivel;
    }

    /** Reconstruye un Usuario a partir de una fila de la base de datos. */
    public static function desdeFila(array $fila): static
    {
        $usuario = new static(
            (int) $fila['cedula'],
            $fila['nom_usuario'],
            $fila['email'],
            $fila['pass_usuario'],
            $fila['pais'],
            (int) $fila['id_nivel']
        );
        $usuario->idUsuario = (int) $fila['id_usuario'];
        $usuario->fechaRegistro = $fila['fecha_registro'] ?? null;
        return $usuario;
    }

    // --- Getters / Setters (según UML) -------------------------------------

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }

    public function setIdUsuario(int $idUsuario): void
    {
        $this->idUsuario = $idUsuario;
    }

    public function getCedula(): int
    {
        return $this->cedula;
    }

    public function setCedula(int $cedula): void
    {
        $this->cedula = $cedula;
    }

    public function getNomUsuario(): string
    {
        return $this->nomUsuario;
    }

    public function setNomUsuario(string $nombre): void
    {
        $this->nomUsuario = $nombre;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassUsuario(): string
    {
        return $this->passUsuario;
    }

    public function setPassUsuario(string $pass): void
    {
        $this->passUsuario = $pass;
    }

    public function getPais(): string
    {
        return $this->pais;
    }

    public function setPais(string $pais): void
    {
        $this->pais = $pais;
    }

    public function getFechaRegistro(): ?string
    {
        return $this->fechaRegistro;
    }

    public function getIdNivel(): int
    {
        return $this->idNivel;
    }

    public function setIdNivel(int $idNivel): void
    {
        $this->idNivel = $idNivel;
    }

    // --- Comportamiento (según UML) -----------------------------------------

    /** Compara una contraseña en texto plano contra el hash guardado. */
    public function autenticar(string $passIngresada): bool
    {
        // Compatibilidad con los usuarios de 02_Datos_prueba.sql, que se
        // cargan en texto plano antes de correr rehash_seed_passwords.php.
        if (!str_starts_with($this->passUsuario, '$2y$') && !str_starts_with($this->passUsuario, '$argon2')) {
            return hash_equals($this->passUsuario, $passIngresada);
        }
        return password_verify($passIngresada, $this->passUsuario);
    }

    /** Genera el hash seguro que debe guardarse en `pass_usuario`. */
    public static function encriptarPassword(string $passPlano): string
    {
        return password_hash($passPlano, PASSWORD_BCRYPT);
    }

    /**
     * Aplica al objeto los campos permitidos de $datos (usado por
     * "editar perfil"). No permite cambiar idUsuario ni idNivel desde acá.
     */
    public function actualizarPerfil(array $datos): bool
    {
        if (isset($datos['nom_usuario'])) {
            $this->setNomUsuario($datos['nom_usuario']);
        }
        if (isset($datos['email']) && self::validarEmailEstatico($datos['email'])) {
            $this->setEmail($datos['email']);
        }
        if (isset($datos['pais'])) {
            $this->setPais($datos['pais']);
        }
        return true;
    }

    public function cerrarSesion(): void
    {
        \Backend\Core\Session::cerrarSesion();
    }

    public function validarEmail(): bool
    {
        return self::validarEmailEstatico($this->email);
    }

    private static function validarEmailEstatico(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /** Representación segura para respuestas JSON (nunca incluye el hash). */
    public function toArray(): array
    {
        return [
            'id_usuario'     => $this->idUsuario,
            'cedula'         => $this->cedula,
            'nom_usuario'    => $this->nomUsuario,
            'email'          => $this->email,
            'pais'           => $this->pais,
            'fecha_registro' => $this->fechaRegistro,
            'id_nivel'       => $this->idNivel,
        ];
    }
}
