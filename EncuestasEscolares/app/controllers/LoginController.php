<?php

require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../helpers/MailHelper.php';
require_once __DIR__ . '/../../config/database.php';

class LoginController
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");

        require_once '../views/login/index.php';
    }

    public function sendCode()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $captchaConfig = require __DIR__ . '/../../config/captcha.php';
        $ip = $_SERVER['REMOTE_ADDR'];

        $matricula = strtoupper(trim($_POST['matricula'] ?? $_SESSION['matricula'] ?? ''));

        if (!$matricula) {
            header('Location: ?controller=login&action=index&error=matricula');
            exit;
        }

        if ($this->verificarIPBloqueadaBD($ip)) {
            header('Location: ?controller=login&action=index&error=ip_blocked');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['force'])) {

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                    die('Acceso no autorizado: Token CSRF inválido.');
                }

                $token = $_POST['recaptcha_token'] ?? '';

                $captcha = json_decode(file_get_contents(
                    "https://www.google.com/recaptcha/api/siteverify?secret={$captchaConfig['secret_key']}&response=$token"
                ), true);

                if (!$captcha['success'] || $captcha['score'] < 0.5) {
                    header('Location: ?controller=login&action=index&error=captcha');
                    exit;
                }
            }

            $userModel = new User();
            $user = $userModel->findByMatricula($matricula);

            if (!$user) {
                header('Location: ?controller=login&action=index&error=matricula');
                exit;
            }

            $expirationTime = strtotime($user['password_time'] ?? '1970-01-01');
            $codigoValido = $user['password_used'] == 0 && (time() - $expirationTime < 600);
            $forzar = isset($_GET['force']) && $_GET['force'] == 1;

            if (!$codigoValido || $forzar) {
                $codigo = bin2hex(random_bytes(4));
                $hashed = password_hash($codigo, PASSWORD_DEFAULT);
                $userModel->guardarCodigoTemporal($user['id'], $hashed);

                if (!MailHelper::enviarCorreoOTP($user['email'], $user['nombre'], $codigo)) {
                    header('Location: ?controller=login&action=index&error=email');
                    exit;
                }
            }

            $_SESSION['matricula'] = $matricula;

            header('Location: ?controller=login&action=index&code_sent=1');
            exit;
        }

        header('Location: ?controller=login&action=index');
        exit;
    }

    public function verify()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $matricula = $_SESSION['matricula'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'];

        if (!$matricula || $this->verificarIPBloqueadaBD($ip)) {
            header('Location: ?controller=login&action=index&error=ip_blocked');
            exit;
        }

        $codigoIngresado = $_POST['codigo'] ?? '';
        $userModel = new User();
        $user = $userModel->findByMatricula($matricula);

        try {
            if (!$user || !isset($user['password_used'], $user['password_time'], $user['temp_password'])) {
                throw new Exception("Datos incompletos en el usuario");
            }

            $expirationTime = strtotime($user['password_time']);
            if ($user['password_used'] == 1 || time() - $expirationTime > 600) {
                $this->registrarIntentoFallidoBD($ip);
                throw new Exception("Código expirado o ya usado");
            }

            if (!password_verify($codigoIngresado, $user['temp_password'])) {
                $this->registrarIntentoFallidoBD($ip);

                $intentosActuales = $this->obtenerIntentosBD($ip);
                if ($intentosActuales >= 3) {
                    header('Location: ?controller=login&action=index&error=ip_blocked');
                    exit;
                }

                $left = max(0, 3 - $intentosActuales);
                header("Location: ?controller=login&action=index&code_sent=1&error=7&left=$left");
                exit;
            }

            $userModel->marcarCodigoUsado($user['id']);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['autenticado'] = true;
            $this->limpiarIntentosBD($ip);
            header('Location: ?controller=survey&action=form');
            exit;

        } catch (Exception $e) {
            error_log("Error en verify: " . $e->getMessage());
            header("Location: ?controller=errors&action=error500");
            exit;
        }
    }

    // BLOQUEO POR IP EN BASE DE DATOS

    private function verificarIPBloqueadaBD($ip)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT baneada, ultima_fecha FROM ip_baneadas WHERE ip = ?");
        $stmt->execute([$ip]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return false;

        $banTime = strtotime($row['ultima_fecha']);
        return $row['baneada'] == 1 && (time() - $banTime < 7200);
    }

    private function registrarIntentoFallidoBD($ip)
    {
        $db = Database::connect();

        $stmt = $db->prepare("SELECT intentos FROM ip_baneadas WHERE ip = ?");
        $stmt->execute([$ip]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $intentos = $row['intentos'] + 1;
            $baneada = $intentos >= 3 ? 1 : 0;

            $update = $db->prepare("UPDATE ip_baneadas SET intentos = ?, ultima_fecha = NOW(), baneada = ? WHERE ip = ?");
            $update->execute([$intentos, $baneada, $ip]);
        } else {
            $insert = $db->prepare("INSERT INTO ip_baneadas (ip, intentos, ultima_fecha, baneada) VALUES (?, 1, NOW(), 0)");
            $insert->execute([$ip]);
        }
    }

    private function limpiarIntentosBD($ip)
    {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE ip_baneadas SET intentos = 0, baneada = 0 WHERE ip = ?");
        $stmt->execute([$ip]);
    }

    private function obtenerIntentosBD($ip)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT intentos FROM ip_baneadas WHERE ip = ?");
        $stmt->execute([$ip]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['intentos'] ?? 0;
    }

    // FUNCIONES PARA DESBLOQUEO POR TOKEN

    public function unlock()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        require_once '../views/login/unlock.php';
    }

    public function sendUnlock()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $matricula = strtoupper(trim($_POST['matricula']));
            $_SESSION['matricula'] = $matricula;

            $userModel = new User();
            $user = $userModel->findByMatricula($matricula);

            if (!$user) {
                header('Location: ?controller=login&action=unlock&error=matricula');
                exit;
            }

            $token = bin2hex(random_bytes(16));
            $userModel->guardarTokenDesbloqueo($user['id'], $token);

            if (MailHelper::enviarCorreoDesbloqueo($user['email'], $user['nombre'], $token)) {
                header('Location: ?controller=login&action=unlock&sent=1');
            } else {
                header('Location: ?controller=login&action=unlock&error=email');
            }
            exit;
        }
    }

    public function unlockConfirm()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $token = $_POST['token'] ?? '';
        $matricula = $_SESSION['matricula'] ?? null;

        if (!$matricula || empty($token)) {
            header('Location: ?controller=login&action=unlock&error=token');
            exit;
        }

        $userModel = new User();
        $user = $userModel->findByMatricula($matricula);

        if (!$user || !$user['unlock_token'] || !$user['unlock_token_created']) {
            header('Location: ?controller=login&action=unlock&error=token');
            exit;
        }

        $tokenTime = strtotime($user['unlock_token_created']);
        if (time() - $tokenTime > 1800) {
            header('Location: ?controller=login&action=unlock&error=token');
            exit;
        }

        if (hash_equals($user['unlock_token'], $token)) {
            $ip = $_SERVER['REMOTE_ADDR'];
            $this->limpiarIntentosBD($ip);
            $userModel->borrarTokenDesbloqueo($user['id']);

            header('Location: ?controller=login&action=index&unlocked=1');
            exit;
        } else {
            header('Location: ?controller=login&action=unlock&error=token');
            exit;
        }
    }
}
