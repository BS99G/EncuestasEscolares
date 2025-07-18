<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header('Location: ?controller=login&action=index');
    exit;
}
$matricula = $_SESSION['matricula'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuesta - Instituto Tecnológico</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/form.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'royal-blue': '#1a237e',
                        'emerald': '#00695c',
                    }
                }
            }
        }
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
            <h1 class="logo text-4xl font-bold mb-2">Encuesta</h1>
            <p class="text-gray-600">Por favor responda todas las preguntas</p>
        </div>

        <form method="POST" action="?controller=survey&action=submit" id="surveyForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <?php foreach ($preguntas as $index => $pregunta): ?>
                <div class="question-card mb-8 p-6 rounded-lg shadow-md border-l-4 border-royal-blue" data-question="<?= $pregunta['id'] ?>">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">
                        <?= ($index + 1) . '. ' . htmlspecialchars($pregunta['texto']) ?>
                    </h3>

                    <?php if ($pregunta['tipo'] === 'radio'): ?>
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-2">
                            <?php foreach ($pregunta['opciones'] as $i => $opcion): ?>
                                <div class="radio-container text-center">
                                    <input 
                                        type="radio" 
                                        name="q_<?= $pregunta['id'] ?>" 
                                        id="q<?= $pregunta['id'] ?>-<?= $i ?>" 
                                        value="<?= htmlspecialchars($opcion['texto']) ?>"
                                    >
                                    <label for="q<?= $pregunta['id'] ?>-<?= $i ?>" class="w-full">
                                        <?= htmlspecialchars($opcion['texto']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($pregunta['tipo'] === 'texto'): ?>
                        <textarea 
                            name="q_<?= $pregunta['id'] ?>" 
                            rows="3" 
                            class="w-full border px-3 py-2 rounded text-sm" 
                            placeholder="Tu respuesta aquí..."></textarea>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="text-center">
                <button type="submit" id="submitSurvey" class="bg-emerald hover:bg-opacity-90 text-white font-bold py-3 px-8 rounded-lg transition-all shadow-md hover:shadow-lg inline-flex items-center">
                    <span>Enviar Respuestas</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <div id="errorMessage" class="hidden bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mt-6 rounded-lg shadow-md" role="alert">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p id="errorText" class="font-medium">Por favor responda todas las preguntas antes de enviar.</p>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function _0x4045(_0x2f2e05,_0x32e040){const _0x2511f7=_0x2511();return _0x4045=function(_0x404522,_0xae3021){_0x404522=_0x404522-0x1c2;let _0x16c32b=_0x2511f7[_0x404522];return _0x16c32b;},_0x4045(_0x2f2e05,_0x32e040);}const _0x3d0255=_0x4045;(function(_0x1f8e51,_0x480995){const _0x43a437=_0x4045,_0x196555=_0x1f8e51();while(!![]){try{const _0x57e339=-parseInt(_0x43a437(0x1dd))/0x1+-parseInt(_0x43a437(0x1cc))/0x2*(-parseInt(_0x43a437(0x1c9))/0x3)+parseInt(_0x43a437(0x1d9))/0x4+parseInt(_0x43a437(0x1da))/0x5*(parseInt(_0x43a437(0x1d2))/0x6)+parseInt(_0x43a437(0x1d6))/0x7+-parseInt(_0x43a437(0x1d0))/0x8*(parseInt(_0x43a437(0x1d1))/0x9)+-parseInt(_0x43a437(0x1db))/0xa;if(_0x57e339===_0x480995)break;else _0x196555['push'](_0x196555['shift']());}catch(_0x54fbc6){_0x196555['push'](_0x196555['shift']());}}}(_0x2511,0x52a1c),document[_0x3d0255(0x1c7)]('DOMContentLoaded',function(){const _0x177413=_0x3d0255,_0x51b067=document[_0x177413(0x1ca)](_0x177413(0x1c5)),_0x469ad6=document[_0x177413(0x1ca)](_0x177413(0x1e1)),_0x3b3bf5=document[_0x177413(0x1ca)](_0x177413(0x1d5));_0x469ad6[_0x177413(0x1c7)]('click',function(_0x4a0821){const _0x115e1c=_0x177413;let _0x555ab5=!![];const _0x1720d0=document[_0x115e1c(0x1d3)](_0x115e1c(0x1c3));_0x1720d0[_0x115e1c(0x1ce)](_0x3bc96e=>_0x3bc96e[_0x115e1c(0x1c2)][_0x115e1c(0x1d7)]('unanswered'));for(let _0x53ece1 of _0x1720d0){const _0x31f9eb=_0x53ece1[_0x115e1c(0x1de)][_0x115e1c(0x1cb)],_0x35fcc9=document[_0x115e1c(0x1e0)](_0x115e1c(0x1c8)+_0x31f9eb+_0x115e1c(0x1d8)),_0x14a9b0=document[_0x115e1c(0x1e0)](_0x115e1c(0x1cd)+_0x31f9eb+'\x22]');if(!_0x35fcc9&&(!_0x14a9b0||_0x14a9b0[_0x115e1c(0x1e3)][_0x115e1c(0x1c4)]()==='')){_0x555ab5=![],_0x53ece1[_0x115e1c(0x1c2)][_0x115e1c(0x1df)](_0x115e1c(0x1d4)),_0x53ece1[_0x115e1c(0x1dc)]({'behavior':'smooth','block':_0x115e1c(0x1e2)});break;}}!_0x555ab5?(_0x4a0821[_0x115e1c(0x1cf)](),_0x51b067[_0x115e1c(0x1c2)][_0x115e1c(0x1d7)](_0x115e1c(0x1c6)),setTimeout(()=>_0x51b067['classList']['add'](_0x115e1c(0x1c6)),0xfa0)):_0x51b067[_0x115e1c(0x1c2)][_0x115e1c(0x1df)](_0x115e1c(0x1c6));});}));function _0x2511(){const _0x17c700=['trim','errorMessage','hidden','addEventListener','input[name=\x22q_','363vPXzsw','getElementById','question','2564etGCGO','textarea[name=\x22q_','forEach','preventDefault','176136QXHiue','162izEqSJ','1177014jeSYgW','querySelectorAll','unanswered','surveyForm','4686794elSUvz','remove','\x22]:checked','1572008DrpgrW','10CzOmVT','5251440AjUmux','scrollIntoView','350094rmNYIh','dataset','add','querySelector','submitSurvey','center','value','classList','.question-card'];_0x2511=function(){return _0x17c700;};return _0x2511();}
</script>
</body>
</html>
