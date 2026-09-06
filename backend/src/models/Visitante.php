<?php

namespace Backend\Models;

/**
 * Visitante (ver UML). No extiende Usuario: representa a alguien anónimo que
 * navega el sitio sin cuenta, identificado sólo por una IP anonimizada.
 */
class Visitante
{
    private int $idVisitante = 0;
    private string $ipAnonima;

    public function __construct(string $ipOriginal)
    {
        $this->ipAnonima = self::anonimizarIp($ipOriginal);
    }

    public static function desdeFila(array $fila): self
    {
        $visitante = new self('0.0.0.0');
        $visitante->idVisitante = (int) $fila['id_visitante'];
        $visitante->ipAnonima = (string) $fila['ip_anonima'];
        return $visitante;
    }

    public function getIdVisitante(): int
    {
        return $this->idVisitante;
    }

    public function setIdVisitante(int $id): void
    {
        $this->idVisitante = $id;
    }

    public function getIpAnonima(): string
    {
        return $this->ipAnonima;
    }

    public function setIpAnonima(string $ip): void
    {
        $this->ipAnonima = $ip;
    }

    /**
     * Anonimiza una IP (IPv4) descartando el último octeto y la representa
     * como un entero, que es como la modela la columna `ip_anonima int(11)`.
     * Se enmascara además el bit más significativo para que el valor entre
     * siempre en el rango de un INT con signo de MySQL.
     */
    public static function anonimizarIp(string $ipOriginal): string
    {
        $partes = explode('.', $ipOriginal);
        if (count($partes) === 4) {
            $partes[3] = '0'; // 192.168.1.57 -> 192.168.1.0
            $ipOriginal = implode('.', $partes);
        }
        $entero = ip2long($ipOriginal);
        if ($entero === false) {
            $entero = crc32($ipOriginal);
        }
        return (string) ($entero & 0x7FFFFFFF);
    }

    /** Registra en el historial que este visitante accedió a una sección. */
    public function registrarAcceso(\Backend\Repositories\HistorialRepository $historiales, string $descripcion): bool
    {
        return $historiales->crear([
            'descrip_historial' => $descripcion,
            'id_usuario'        => null,
            'id_visitante'      => $this->idVisitante,
        ]) > 0;
    }
}
