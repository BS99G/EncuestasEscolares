<?php
// config/database.php
require_once __DIR__ . '/../helpers/env.php';
class Database {
    private static $host = env('DB_HOST');
    private static $dbName = env('DB_NAME');
    private static $username = env('DB_USER');
    private static $password = env('DB_PASS');
    private static $conn;

    public static function connect() {
        if (!self::$conn) {
            try {
                // PHP timezone
                date_default_timezone_set('America/Mexico_City');

                self::$conn = new PDO(
                    'mysql:host=' . self::$host . ';dbname=' . self::$dbName . ';charset=utf8mb4',
                    self::$username,
                    self::$password
                );
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                self::$conn->exec("SET time_zone = '-06:00'");

            } catch (PDOException $e) {
                die('Error de conexión: ' . $e->getMessage());
            }
        }

        return self::$conn;
    }
}
