<?php
class Model {
    protected $db;

    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $this->db = Database::connect();
    }
}
