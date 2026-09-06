<?php

namespace Backend\Repositories;

use Backend\Config\Database;
use Backend\Models\Noticia;
use PDO;

/** CRUD (DML con sentencias preparadas) de `Noticias`. */
class NoticiaRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConexion();
    }

    public function buscarPorId(int $idNoticia): ?Noticia
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Noticias WHERE id_noticia = :id LIMIT 1');
        $stmt->execute([':id' => $idNoticia]);
        $fila = $stmt->fetch();
        return $fila ? Noticia::desdeFila($fila) : null;
    }

    /** @return Noticia[] */
    public function listar(int $limite = 50): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Noticias ORDER BY fecha_noticia DESC LIMIT :limite');
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return array_map(fn (array $fila) => Noticia::desdeFila($fila), $stmt->fetchAll());
    }

    public function crear(array $datos): int
    {
        $sql = 'INSERT INTO Noticias (descrip_noticia, id_usuario, aviso_polideportivo_compartido)
                VALUES (:descrip, :id_usuario, :aviso)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':descrip'    => $datos['descrip_noticia'],
            ':id_usuario' => $datos['id_usuario'],
            ':aviso'      => !empty($datos['aviso_polideportivo_compartido']) ? 1 : 0,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function actualizar(int $idNoticia, array $datos): bool
    {
        $sql = 'UPDATE Noticias SET descrip_noticia = :descrip, aviso_polideportivo_compartido = :aviso WHERE id_noticia = :id';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':descrip' => $datos['descrip_noticia'],
            ':aviso'   => !empty($datos['aviso_polideportivo_compartido']) ? 1 : 0,
            ':id'      => $idNoticia,
        ]);
    }

    public function eliminar(int $idNoticia): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM Noticias WHERE id_noticia = :id');
        return $stmt->execute([':id' => $idNoticia]);
    }
}
