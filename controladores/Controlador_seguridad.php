<?php
// Inicia la sesión únicamente si no ha sido iniciada previamente
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar la presencia del token de seguridad generado durante el login
if (!isset($_SESSION['token_seguridad']) || empty($_SESSION['token_seguridad'])) {
    // Limpia cualquier residuo de sesión inválida
    session_unset();
    session_destroy();

    // Redirecciona al index en la raíz del servidor y detiene la ejecución
    header("Location: /index.php");
    exit();
}