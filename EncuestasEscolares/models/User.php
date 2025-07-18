<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Busca un usuario por matrícula
     */
    public function findByMatricula(string $matricula): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE matricula = :matricula LIMIT 1");
        $stmt->execute([':matricula' => $matricula]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Guarda una contraseña temporal (hash) con marca de tiempo
     */
    public function guardarCodigoTemporal(int $id, string $hashedPassword): void
    {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET temp_password = :password, password_used = 0, password_time = NOW() 
            WHERE id = :id
        ");
        $stmt->execute([
            ':password' => $hashedPassword,
            ':id' => $id
        ]);
    }

    /**
     * Marca una contraseña temporal como usada
     */
    public function marcarCodigoUsado(int $id): void
    {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET password_used = 1 
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
    }

    /**
     * Guarda un token de desbloqueo con timestamp
     */
    public function guardarTokenDesbloqueo(int $id, string $token): void
    {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET unlock_token = :token, unlock_token_created = NOW() 
            WHERE id = :id
        ");
        $stmt->execute([
            ':token' => $token,
            ':id' => $id
        ]);
    }

    /**
     * Borra un token de desbloqueo después de usarlo
     */
    public function borrarTokenDesbloqueo(int $id): void
    {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET unlock_token = NULL, unlock_token_created = NULL 
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
    }

    /**
     * Verifica si la contraseña temporal ya expiró (ej. 10 minutos)
     */
    public function contrasenaTemporalExpirada(int $id, int $expiraMinutos = 10): bool
    {
        $stmt = $this->db->prepare("
            SELECT password_time 
            FROM users 
            WHERE id = :id AND password_used = 0
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['password_time'])) {
            return true;
        }

        $tiempo = strtotime($row['password_time']);
        return (time() - $tiempo) > ($expiraMinutos * 60);
    }
}
