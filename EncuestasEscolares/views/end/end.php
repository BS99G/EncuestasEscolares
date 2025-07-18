<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_destroy();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5;url=?controller=login&action=index">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gracias - Instituto Tecnológico</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/end.css">
    <script>
        setTimeout(() => {
            window.location.href = '?controller=login&action=index';
        }, 5000);
    </script>
</head>
<body class="pt-16 pb-8 px-4">
    <nav class="fixed top-0 left-0 w-full bg-black text-white p-4 shadow-lg z-10">
        <div class="container mx-auto">
            <h1 class="text-2xl font-bold">Instituto Tecnológico</h1>
        </div>
    </nav>

    <div class="container mx-auto max-w-4xl">
        <div class="survey-container rounded-xl shadow-2xl p-8 border-t-4 border-royal-blue">
            <div class="text-center mb-8">
                <h1 class="logo text-4xl font-bold mb-2">Encuesta Completada</h1>
            </div>

            <div id="thankYouMessage" class="text-center py-12">
                <div class="bg-green-100 rounded-full p-6 inline-block mb-6 shadow-lg animate-pulse-slow">
                    <svg class="h-24 w-24 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" class="animate-fadeIn"></path>
                    </svg>
                </div>
                <div class="animate-fadeIn">
                    <h2 class="text-3xl font-bold text-gray-800 mt-6">¡Gracias por contestar la encuesta!</h2>
                    <p class="text-gray-600 mt-4 text-xl">Sus respuestas han sido enviadas correctamente.</p>
                    <p class="text-gray-500 mt-8 text-lg">Valoramos mucho su opinión y nos ayudará a mejorar nuestros servicios.</p>

                    <div class="mt-10 border-t border-gray-200 pt-8">
                        <p class="text-emerald font-medium">Redirigiendo al inicio de sesión en 5 segundos...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
