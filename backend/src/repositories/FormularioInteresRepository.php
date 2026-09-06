<?php

namespace Backend\Repositories;

use Backend\Config\Database;
use Backend\Models\FormularioInteres;
use PDO;

/** CRUD (DML con sentencias preparadas) de `Formularios_interes`. */
class FormularioInteresRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConexion();
    }

    public function buscarPorId(int $idFormulario): ?FormularioInteres
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Formularios_interes WHERE id_formulario = :id LIMIT 1');
        $stmt->execute([':id' => $idFormulario]);
        $fila = $stmt->fetch();
        return $fila ? FormularioInteres::desdeFila($fila) : null;
    }

    /**
     * @return array Filas con datos del alumno (join) para la tabla del panel de administración.
     */
    public function listarConDatosUsuario(): array
    {
        $sql = 'SELECT f.*, u.nom_usuario, u.email, u.cedula
                FROM Formularios_interes f
                INNER JOIN Usuarios u ON u.id_usuario = f.id_usuario
                ORDER BY f.fecha_form DESC';
        return $this->pdo->query($sql)->fetchAll();
    }

    public function crear(array $datos): int
    {
        $sql = 'INSERT INTO Formularios_interes
                (curso_lista_interes, descrip_formulario, mensaje_usuario, id_usuario, aviso_cupo_aceptado, archivo_comprobante)
                VALUES (:curso, :descrip, :mensaje, :id_usuario, :aviso_cupo, :archivo)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':curso'      => $datos['curso_lista_interes'],
            ':descrip'    => $datos['descrip_formulario'],
            ':mensaje'    => $datos['mensaje_usuario'] ?? null,
            ':id_usuario' => $datos['id_usuario'],
            ':aviso_cupo' => !empty($datos['aviso_cupo_aceptado']) ? 1 : 0,
            ':archivo'    => $datos['archivo_comprobante'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function eliminar(int $idFormulario): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM Formularios_interes WHERE id_formulario = :id');
        return $stmt->execute([':id' => $idFormulario]);
    }
}
