<?php

class EndController
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Mostrar el diseño final de agradecimiento
        require_once __DIR__ . '/../../views/end/end.php';
    }
}
