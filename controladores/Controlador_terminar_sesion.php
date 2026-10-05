<?php
session_start();

// Elimina todas las variables de sesión y destruye la sesión
$_SESSION = [];
session_unset();
session_destroy();

// Redirige al login
header("Location: /index.php");
exit();