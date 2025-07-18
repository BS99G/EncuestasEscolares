<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Encuestas Estudiantiles</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://www.google.com/recaptcha/api.js?render=6LfpxH4rAAAAAINGnR1bcjkGoNfbZ9ceGuskFQ1i"></script>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="pt-16 pb-8 px-4 bg-gray-50">
<nav class="fixed top-0 left-0 w-full bg-black text-white p-4 shadow-lg z-10">
    <div class="container mx-auto">
        <h1 class="text-2xl font-bold">Instituto Tecnológico</h1>
    </div>
</nav>

<div class="container mx-auto max-w-md">
    <div class="login-container rounded-xl shadow-2xl p-8 bg-white mt-16 relative z-10">
        <div class="text-center mb-6">
            <?php if (isset($_GET['error'])): ?>
                <div id="alertError" class="bg-red-100 text-red-800 p-3 rounded mb-4 text-sm">
                    <?php
                    switch ($_GET['error']) {
                        case 'captcha': echo "⚠️ Falló la verificación de seguridad."; break;
                        case 'matricula': echo "⚠️ Matrícula no encontrada."; break;
                        case 'email': echo "⚠️ Error al enviar el código al correo."; break;
                        case 'expired': echo "⚠️ El código ha expirado. Solicita uno nuevo."; break;
                        case '6': echo "⚠️ Código inválido o ya utilizado."; break;
                        case '7':
                            $left = $_GET['left'] ?? '?';
                            echo "⚠️ Código incorrecto. Te quedan <strong>$left</strong> intento(s).";
                            break;
                        case 'ip_blocked':
                            echo "🚫 Tu IP ha sido bloqueada temporalmente por múltiples intentos fallidos.";
                            break;
                        case 'token':
                            echo "⚠️ Token de desbloqueo inválido o expirado."; break;
                        default: echo "⚠️ Error desconocido.";
                    }
                    ?>
                </div>

                <?php if ($_GET['error'] === 'ip_blocked'): ?>
                    <div class="text-center text-sm mb-4">
                        ¿Estás bloqueado? Puedes <a href="?controller=login&action=unlock" class="text-blue-700 font-semibold underline">solicitar el desbloqueo aquí</a>.
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($_GET['code_sent'])): ?>
                <div id="alertSuccess" class="bg-green-100 text-green-800 p-3 rounded mb-4 text-sm">
                    ✅ Código enviado, revisa tu correo.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['code_sent'])): ?>
                <div class="text-center text-sm mt-2">
                    ¿No recibiste el código?
                    <a href="?controller=login&action=sendCode&force=1" class="text-blue-700 underline font-semibold">Reenviar código</a>
                </div>
            <?php endif; ?>


            <?php if (isset($_GET['unlocked'])): ?>
                <div class="bg-green-100 text-green-800 p-3 rounded mb-4 text-sm">
                    🔓 IP desbloqueada correctamente. Ya puedes volver a intentar iniciar sesión.
                </div>
            <?php endif; ?>

            <h1 class="text-3xl font-bold text-gray-800">Encuestas Estudiantiles</h1>
            <p class="text-gray-600">Acceso al sistema de encuestas</p>
        </div>

        <!-- Paso 1: CAPTCHA -->
        <div id="step1" class="step <?php echo isset($_GET['code_sent']) ? '' : 'active'; ?>">
            <button id="captchaBtn"
                    class="w-full py-3 rounded bg-gradient-to-r from-indigo-800 to-emerald-700 text-white font-semibold"
                    <?php if (isset($_GET['code_sent'])) echo 'style="display:none;"'; ?>>
                Verificar Seguridad (CAPTCHA)
            </button>
        </div>

        <!-- Paso 2: Matrícula -->
        <form id="matriculaForm" class="step mt-4 <?php echo isset($_GET['code_sent']) ? '' : ''; ?>"
              method="POST" action="?controller=login&action=sendCode">
            <input type="hidden" name="recaptcha_token" id="recaptcha_token">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
            <label for="matricula" class="block text-gray-700 mb-2">Matrícula</label>
            <input type="text" name="matricula" id="matricula"
                   placeholder="Ej: E-IT12345"
                   class="w-full px-4 py-3 rounded border border-gray-300 mb-4 uppercase"
                   maxlength="10" required>
            <button type="submit"
                    class="w-full py-3 rounded bg-gradient-to-r from-indigo-800 to-emerald-700 text-white font-semibold">
                Enviar código al correo
            </button>
            <div id="loadingMessage" class="hidden text-center mt-2 text-sm text-gray-500">
                Enviando código... espera un momento
            </div>
        </form>

        <!-- Paso 3: Código -->
        <?php if (isset($_GET['code_sent']) && isset($_SESSION['matricula'])): ?>
            <form id="codeForm" class="step mt-4 active" method="POST" action="?controller=login&action=verify">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <label for="codigo" class="block text-gray-700 mb-2">Código enviado a tu correo</label>
                <input type="text" name="codigo" id="codigo"
                       placeholder="Ej: 123456"
                       class="w-full px-4 py-3 rounded border border-gray-300 mb-4"
                       required>
                <button type="submit"
                        class="w-full py-3 rounded bg-gradient-to-r from-indigo-800 to-emerald-700 text-white font-semibold">
                    Verificar código
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>
<script src="assets/js/login.js"></script>
</body>
</html>
