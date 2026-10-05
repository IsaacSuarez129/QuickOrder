<?php
// Controlador de autenticación y redirección por rol
session_start();

require_once __DIR__ . '/../configuracion/conexion_bd.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario_ingresado = isset($_POST["usuarios"]) ? trim($_POST["usuarios"]) : '';
    $contrasena_ingresada = isset($_POST["contrasena"]) ? trim($_POST["contrasena"]) : '';

    if (empty($usuario_ingresado) || empty($contrasena_ingresada)) {
        echo "<script>
                alert('Por favor, llena todos los campos');
                window.location.href = '../index.php';
              </script>";
        exit();
    }

    $bd = new BaseDatos();
    $conexion = $bd->obtenerConexion();

    if ($conexion) {
        // 1. Verificar si el usuario existe en la base de datos
        $query = "SELECT * FROM usuarios WHERE usuario = :usuario LIMIT 1";
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(":usuario", $usuario_ingresado);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            // Validar la contraseña
            if ($contrasena_ingresada === $fila['contrasena']) {
                // 2. Generar token de seguridad y registrar la sesión
                $_SESSION['token_seguridad'] = bin2hex(random_bytes(16));
                $_SESSION['usuario_activo'] = $fila['usuario'];

                // 3. Obtener la primera letra del usuario para redireccionar
                $primera_letra = strtolower(substr($fila['usuario'], 0, 1));

                // 4. Redireccionar al dashboard correspondiente
                switch ($primera_letra) {
                    case 'a': // Administrador (ej. admin_isaac)
                        header("Location: ../vistas/admin/dashboard.php");
                        break;
                    case 'c': // Chef / Cocina (ej. carlos)
                        header("Location: ../vistas/cocina/dashboard.php");
                        break;
                    case 'j': // Cajero / Caja (ej. juan)
                        header("Location: ../vistas/caja/dashboard.php");
                        break;
                    default:  // Cliente u otros roles
                        header("Location: ../vistas/cliente/dashboard.php");
                        break;
                }
                exit();
            } else {
                echo "<script>
                        alert('Contraseña incorrecta');
                        window.location.href = '../index.php';
                      </script>";
                exit();
            }
        } else {
            // El usuario no existe en la base de datos
            echo "<script>
                    alert('El usuario no existe');
                    window.location.href = '../index.php';
                  </script>";
            exit();
        }
    } else {
        echo "<script>
                alert('Error al conectar con la base de datos');
                window.location.href = '../index.php';
              </script>";
        exit();
    }
}
?>