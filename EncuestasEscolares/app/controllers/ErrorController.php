<?php

class ErrorController
{
    public function error403()
    {
        http_response_code(403);
        require_once __DIR__ . '/../../views/errors/403.php';
    }

    public function error404()
    {
        http_response_code(404);
        require_once __DIR__ . '/../../views/errors/404.php';
    }

    public function error500()
    {
        http_response_code(500);
        require_once __DIR__ . '/../../views/errors/500.php';
    }
}
