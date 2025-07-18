<?php

require_once __DIR__ . '/../config/database.php';

class BloqueoIPModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function estaBloqueada($ip): bool
    {
        $stmt = $this->db->prepare("SELECT bloqueado_hasta FROM bloqueos_ip WHERE ip = ?");
        $stmt->execute([$ip]);
        $bloqueado = $stmt->fetchColumn();
        return $bloqueado && strtotime($bloqueado) > time();
    }

    public function registrarIntento($ip): void
    {
        $stmt = $this->db->prepare("SELECT intentos FROM bloqueos_ip WHERE ip = ?");
        $stmt->execute([$ip]);
        $intentos = $stmt->fetchColumn();

        if ($intentos !== false) {
            $intentos++;
            if ($intentos >= 3) {
                $stmt = $this->db->prepare("UPDATE bloqueos_ip SET intentos = 0, bloqueado_hasta = DATE_ADD(NOW(), INTERVAL 2 HOUR) WHERE ip = ?");
                $stmt->execute([$ip]);
            } else {
                $stmt = $this->db->prepare("UPDATE bloqueos_ip SET intentos = ?, ultimo_intento = NOW() WHERE ip = ?");
                $stmt->execute([$intentos, $ip]);
            }
        } else {
            $stmt = $this->db->prepare("INSERT INTO bloqueos_ip (ip, intentos) VALUES (?, 1)");
            $stmt->execute([$ip]);
        }
    }

    public function obtenerIntentos($ip): int
    {
        $stmt = $this->db->prepare("SELECT intentos FROM bloqueos_ip WHERE ip = ?");
        $stmt->execute([$ip]);
        return (int) $stmt->fetchColumn();
    }

    public function limpiarIntentos($ip): void
    {
        $stmt = $this->db->prepare("DELETE FROM bloqueos_ip WHERE ip = ?");
        $stmt->execute([$ip]);
    }

    public function eliminarBloqueo($ip): void
    {
        $this->limpiarIntentos($ip);
    }
}
