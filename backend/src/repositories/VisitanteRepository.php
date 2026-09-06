<?php

namespace Backend\Repositories;

use Backend\Config\Database;
use Backend\Models\Visitante;
use PDO;

class VisitanteRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConexion();
    }

    public function buscarPorIpAnonima(string $ipAnonima): ?Visitante
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Visitantes WHERE ip_anonima = :ip LIMIT 1');
        $stmt->execute([':ip' => $ipAnonima]);
        $fila = $stmt->fetch();
        return $fila ? Visitante::desdeFila($fila) : null;
    }

    /** Reutiliza el registro de Visitante si ya existe uno con esa IP anonimizada; si no, lo crea. */
    public function buscarOCrear(string $ipOriginal): Visitante
    {
        $visitante = new Visitante($ipOriginal);
        $existente = $this->buscarPorIpAnonima($visitante->getIpAnonima());
        if ($existente !== null) {
            return $existente;
        }

        $stmt = $this->pdo->prepare('INSERT INTO Visitantes (ip_anonima) VALUES (:ip)');
        $stmt->execute([':ip' => $visitante->getIpAnonima()]);
        $visitante->setIdVisitante((int) $this->pdo->lastInsertId());
        return $visitante;
    }
}
