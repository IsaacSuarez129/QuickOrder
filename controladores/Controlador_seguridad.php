<?php
// Inicia la sesión únicamente si no ha sido iniciada previamente
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar la presencia del token de seguridad generado durante el login Y el rol
if (!isset($_SESSION['token_seguridad']) || empty($_SESSION['token_seguridad']) || !isset($_SESSION['rol'])) {
    // Limpia cualquier residuo de sesión inválida
    session_unset();
    session_destroy();

    // Redirecciona al index de tu proyecto y detiene la ejecución
    header("Location: ../../index.php");
    exit();
}

function verificarAcceso($roles_permitidos) {
    $mi_rol = $_SESSION['rol'];

    // Si el rol del usuario NO está en la lista de los que pueden ver esta pantalla...
    if (!in_array($mi_rol, $roles_permitidos)) {
        
        // Lo mandamos de regreso a SU pantalla correspondiente
        switch ($mi_rol) {
            case 'admin':
                header('Location: ../admin/admin.php');
                break;
            case 'chef':
                header('Location: ../cocina/cocina.php');
                break;
            case 'cajero':
                header('Location: ../caja/caja.php');
                break;
            default:
                header('Location: ../../index.php');
                break;
        }
        exit(); // Cortamos la carga para que no se vea el HTML oculto
    }
}
?>