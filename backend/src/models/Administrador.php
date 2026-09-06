<?php

namespace Backend\Models;

/** Administrador (ver UML). Extiende Usuario y agrega `cargo`. */
class Administrador extends Usuario
{
    private string $cargo;

    public function __construct(
        int $cedula,
        string $nomUsuario,
        string $email,
        string $passUsuario,
        string $pais,
        int $idNivel,
        string $cargo
    ) {
        parent::__construct($cedula, $nomUsuario, $email, $passUsuario, $pais, $idNivel);
        $this->cargo = $cargo;
    }

    public static function desdeFila(array $fila): static
    {
        $admin = new static(
            (int) $fila['cedula'],
            $fila['nom_usuario'],
            $fila['email'],
            $fila['pass_usuario'],
            $fila['pais'],
            (int) $fila['id_nivel'],
            $fila['cargo'] ?? ''
        );
        $admin->idUsuario = (int) $fila['id_usuario'];
        $admin->fechaRegistro = $fila['fecha_registro'] ?? null;
        return $admin;
    }

    public function getCargo(): string
    {
        return $this->cargo;
    }

    public function setCargo(string $cargo): void
    {
        $this->cargo = $cargo;
    }

    /**
     * publicarNoticia / editarNoticia / eliminarNoticia, crearOfertaEducativa /
     * editarOfertaEducativa / cambiarEstadoOferta, gestionarUsuarios, etc. del
     * UML se implementan como flujos de NoticiaService y OfertaEducativaService
     * (reciben quién ejecuta la acción y delegan el guardado en el repositorio
     * correspondiente), en vez de vivir como métodos de esta entidad: así el
     * acceso a datos queda centralizado y testeable en un solo lugar.
     */

    public function toArray(): array
    {
        return parent::toArray() + ['cargo' => $this->cargo];
    }
}
