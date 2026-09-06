<?php

namespace Backend\Repositories;

use Backend\Config\Database;
use Backend\Models\OfertaEducativa;
use PDO;

/** CRUD (DML con sentencias preparadas) de `Ofertas_educativas`. */
class OfertaEducativaRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConexion();
    }

    public function buscarPorId(int $idOferta): ?OfertaEducativa
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Ofertas_educativas WHERE id_oferta = :id LIMIT 1');
        $stmt->execute([':id' => $idOferta]);
        $fila = $stmt->fetch();
        return $fila ? OfertaEducativa::desdeFila($fila) : null;
    }

    /** @return OfertaEducativa[] */
    public function listar(?string $estado = null): array
    {
        $sql = 'SELECT * FROM Ofertas_educativas';
        $params = [];
        if ($estado !== null) {
            $sql .= ' WHERE estado_oferta = :estado';
            $params[':estado'] = $estado;
        }
        $sql .= ' ORDER BY id_oferta DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return array_map(fn (array $fila) => OfertaEducativa::desdeFila($fila), $stmt->fetchAll());
    }

    public function crear(array $datos): int
    {
        $sql = 'INSERT INTO Ofertas_educativas
                (nom_oferta, descrip_oferta, `duración_oferta`, estado_oferta, requisitos, perfil_egreso, id_usuario, id_turno, archivo_programa_pdf)
                VALUES (:nom, :descrip, :duracion, :estado, :requisitos, :perfil, :id_usuario, :id_turno, :archivo)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nom'        => $datos['nom_oferta'],
            ':descrip'    => $datos['descrip_oferta'],
            ':duracion'   => $datos['duracion_oferta'],
            ':estado'     => $datos['estado_oferta'] ?? 'vigente',
            ':requisitos' => $datos['requisitos'] ?? null,
            ':perfil'     => $datos['perfil_egreso'],
            ':id_usuario' => $datos['id_usuario'],
            ':id_turno'   => $datos['id_turno'],
            ':archivo'    => $datos['archivo_programa_pdf'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function actualizar(int $idOferta, array $datos): bool
    {
        $sql = 'UPDATE Ofertas_educativas SET
                    nom_oferta = :nom, descrip_oferta = :descrip, `duración_oferta` = :duracion,
                    estado_oferta = :estado, requisitos = :requisitos, perfil_egreso = :perfil,
                    id_turno = :id_turno
                WHERE id_oferta = :id';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nom'        => $datos['nom_oferta'],
            ':descrip'    => $datos['descrip_oferta'],
            ':duracion'   => $datos['duracion_oferta'],
            ':estado'     => $datos['estado_oferta'],
            ':requisitos' => $datos['requisitos'] ?? null,
            ':perfil'     => $datos['perfil_egreso'],
            ':id_turno'   => $datos['id_turno'],
            ':id'         => $idOferta,
        ]);
    }

    public function cambiarEstado(int $idOferta, string $estado): bool
    {
        $stmt = $this->pdo->prepare('UPDATE Ofertas_educativas SET estado_oferta = :estado WHERE id_oferta = :id');
        return $stmt->execute([':estado' => $estado, ':id' => $idOferta]);
    }

    public function eliminar(int $idOferta): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM Ofertas_educativas WHERE id_oferta = :id');
        return $stmt->execute([':id' => $idOferta]);
    }

    /** @return \Backend\Models\Curso[] Cursos asociados vía la tabla `Contienen`. */
    public function listarCursosAsociados(int $idOferta): array
    {
        $sql = 'SELECT c.* FROM Cursos c
                INNER JOIN Contienen co ON co.id_curso = c.id_curso
                WHERE co.id_oferta = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $idOferta]);
        return array_map(fn (array $fila) => \Backend\Models\Curso::desdeFila($fila), $stmt->fetchAll());
    }
}
