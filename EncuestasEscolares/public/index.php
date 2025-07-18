<?php
// public/index.php

session_start();

require_once '../config/database.php';    // conexión a la base
require_once '../core/Router.php';        // enrutador principal
require_once '../core/Model.php';         // clase base de modelo

// Cargar todos los controladores automáticamente
foreach (glob('../app/controllers/*.php') as $file) {
    require_once $file;
}

// Iniciar el enrutamiento
$router = new Router();
$router->route();
