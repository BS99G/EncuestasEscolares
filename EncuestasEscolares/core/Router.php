<?php
// core/Router.php

class Router
{
    public function route()
    {
        try {
            $controllerName = $_GET['controller'] ?? 'login';
            $actionName = $_GET['action'] ?? 'index';

            $controllerClass = ucfirst($controllerName) . 'Controller';
            $controllerPath = __DIR__ . '/../app/controllers/' . $controllerClass . '.php';

            // Verifica que el archivo y la clase existan
            if (!file_exists($controllerPath)) {
                header('Location: ?controller=error&action=error404');
                exit;
            }

            require_once $controllerPath;

            if (!class_exists($controllerClass)) {
                header('Location: ?controller=error&action=error404');
                exit;
            }

            $controller = new $controllerClass();

            if (!method_exists($controller, $actionName)) {
                header('Location: ?controller=error&action=error404');
                exit;
            }

            $controller->$actionName();

        } catch (Throwable $e) {
            // Puedes registrar el error aquí si quieres: file_put_contents(), etc.
            header('Location: ?controller=error&action=error500');
            exit;
        }
    }
}
