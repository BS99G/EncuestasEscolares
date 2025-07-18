<?php 
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
} 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Desbloquear acceso - Encuestas Estudiantiles</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
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
                <div class="bg-red-100 text-red-800 p-3 rounded mb-4 text-sm">
                    <?php
                    switch ($_GET['error']) {
                        case 'matricula': echo "⚠️ Matrícula no encontrada."; break;
                        case 'email': echo "⚠️ Error al enviar el correo."; break;
                        case 'token': echo "⚠️ Token inválido o expirado."; break;
                        default: echo "⚠️ Error desconocido.";
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['sent'])): ?>
                <div class="bg-green-100 text-green-800 p-3 rounded mb-4 text-sm">
                    ✅ Token enviado correctamente. Revisa tu correo electrónico.
                </div>
            <?php endif; ?>

            <h1 class="text-3xl font-bold text-gray-800">Encuestas Estudiantiles</h1>
            <p class="text-gray-600">Desbloqueo de acceso por IP</p>
        </div>

        <?php if (!isset($_GET['sent'])): ?>
            <!-- Formulario para ingresar matrícula -->
            <form method="POST" action="?controller=login&action=sendUnlock">
                <label for="matricula" class="block text-gray-700 mb-2">Matrícula</label>
                <input type="text" name="matricula" id="matricula"
                       value="<?php echo htmlspecialchars($_SESSION['matricula'] ?? ''); ?>"
                       placeholder="Ej: E-IT99999"
                       class="w-full px-4 py-3 rounded border border-gray-300 mb-4 uppercase"
                       maxlength="10" required>
                <button type="submit"
                        class="w-full py-3 rounded bg-gradient-to-r from-indigo-800 to-emerald-700 text-white font-semibold">
                    Verificar token y desbloquear IP
                </button>
                <?php 
                    // ⚠️ Guardar la matrícula en sesión para validación del token
                    if (isset($_POST['matricula'])) {
                        $_SESSION['matricula'] = $_POST['matricula'];
                    }
                ?>
            </form>
        <?php endif; ?>

        <?php if (isset($_GET['sent'])): ?>
            <!-- Formulario para ingresar token -->
            <form method="POST" action="?controller=login&action=unlockConfirm" class="mt-4">
                <label for="token" class="block text-gray-700 mb-2">Token recibido</label>
                <input type="text" name="token" id="token"
                       placeholder="Ej: abcdef123456..."
                       class="w-full px-4 py-3 rounded border border-gray-300 mb-4"
                       required>
                <button type="submit"
                        class="w-full py-3 rounded bg-gradient-to-r from-indigo-800 to-emerald-700 text-white font-semibold">
                    Verificar token y desbloquear IP
                </button>
            </form>
        <?php endif; ?>

        <div class="text-center mt-6">
            <a href="?controller=login&action=index" class="text-blue-700 text-sm underline">← Volver al inicio</a>
        </div>
    </div>
</div>
</body>
</html>
