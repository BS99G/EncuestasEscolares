<?php

require_once __DIR__ . '/../../models/Survey.php';

class SurveyController
{
    private $inactividad = 300; // 5 minutos

    private function iniciarSesionSegura()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Cabeceras de seguridad
        header("X-Frame-Options: SAMEORIGIN");
        header("X-Content-Type-Options: nosniff");
        header("X-XSS-Protection: 1; mode=block");

        // Expiración por inactividad
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $this->inactividad) {
            session_unset();
            session_destroy();
            header('Location: ?controller=login&action=index');
            exit;
        }
        $_SESSION['LAST_ACTIVITY'] = time();
    }

    private function validarMatriculaActiva($matricula)
    {
        $surveyModel = new Survey();
        return $surveyModel->validarMatricula($matricula);
    }

    public function form()
    {
        $this->iniciarSesionSegura();

        if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
            header('Location: ?controller=login&action=index');
            exit;
        }

        $matricula = $_SESSION['matricula'];
        $surveyModel = new Survey();

        // Verificación adicional de matrícula
        if (!$this->validarMatriculaActiva($matricula)) {
            session_destroy();
            header('Location: ?controller=login&action=index');
            exit;
        }

        if ($surveyModel->yaRespondio($matricula)) {
            header('Location: ?controller=end&action=index');
            exit;
        }

        $preguntas = $surveyModel->obtenerPreguntasActivas();

        foreach ($preguntas as &$pregunta) {
            if ($pregunta['tipo'] === 'radio') {
                $pregunta['opciones'] = $surveyModel->obtenerOpciones($pregunta['id']);
            }
        }
        unset($pregunta);

        // Generar token CSRF nuevo
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        require_once __DIR__ . '/../../views/survey/form.php';
    }

    public function submit()
    {
        $this->iniciarSesionSegura();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?controller=survey&action=form');
            exit;
        }

        if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
            header('Location: ?controller=login&action=index');
            exit;
        }

        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            die("Token CSRF inválido.");
        }

        // Invalida el token inmediatamente después de usarlo
        unset($_SESSION['csrf_token']);

        $matricula = $_SESSION['matricula'];
        $modelo = new Survey();

        // Verificación de reintento o acceso indebido
        if ($modelo->yaRespondio($matricula)) {
            header('Location: ?controller=end&action=index');
            exit;
        }

        // Validar que matrícula esté activa
        if (!$this->validarMatriculaActiva($matricula)) {
            session_destroy();
            header('Location: ?controller=login&action=index');
            exit;
        }

        $preguntas = $modelo->obtenerPreguntasActivas();
        $errores = [];
        $respuestas = [];

        foreach ($preguntas as $pregunta) {
            $id = $pregunta['id'];
            $key = "q_$id";

            if (!isset($_POST[$key]) || trim($_POST[$key]) === '') {
                $errores[] = $id;
            } else {
                $valor = strip_tags(trim($_POST[$key]));
                $valor = htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');

                if (strlen($valor) > 255) {
                    $valor = substr($valor, 0, 255);
                }

                $respuestas[$id] = $valor;
            }
        }

        if (!empty($errores)) {
            $_SESSION['validation_error'] = "Debes responder todas las preguntas.";
            $_SESSION['unanswered'] = $errores;
            $_SESSION['old_inputs'] = $_POST;
            header('Location: ?controller=survey&action=form');
            exit;
        }

        foreach ($respuestas as $preguntaId => $respuesta) {
            // Validación de integridad
            if (!is_numeric($preguntaId) || !is_string($respuesta)) {
                die("Datos inválidos detectados.");
            }

            $modelo->guardarRespuesta($matricula, $preguntaId, $respuesta);
        }

        header('Location: ?controller=end&action=index');
        exit;
    }
}
