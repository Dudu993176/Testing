<?php

namespace Backend\Repositories;

use Backend\Config\Database;
use Backend\Models\NivelAcceso;
use PDO;

class NivelAccesoRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConexion();
    }

    public function buscarPorId(int $idNivel): ?NivelAcceso
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Niveles_acceso WHERE id_nivel = :id LIMIT 1');
        $stmt->execute([':id' => $idNivel]);
        $fila = $stmt->fetch();
        return $fila ? NivelAcceso::desdeFila($fila) : null;
    }

    /** @return NivelAcceso[] */
    public function listar(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM Niveles_acceso ORDER BY id_nivel');
        return array_map(fn (array $fila) => NivelAcceso::desdeFila($fila), $stmt->fetchAll());
    }
}
