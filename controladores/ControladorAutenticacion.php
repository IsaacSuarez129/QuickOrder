<?php
// Controlador de autenticación y redirección por rol
session_start();

require_once __DIR__ . '/../configuracion/conexion_bd.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario_ingresado = isset($_POST["usuarios"]) ? trim($_POST["usuarios"]) : '';
    $contrasena_ingresada = isset($_POST["contrasena"]) ? trim($_POST["contrasena"]) : '';

    // 1. Validar campos vacíos
    if (empty($usuario_ingresado) || empty($contrasena_ingresada)) {
        echo "<script>
                alert('Por favor, llena todos los campos');
                window.location.href = '../index.php';
              </script>";
        exit();
    }

    // 2. Validación de mínimo 8 caracteres (usando strlen nativo)
    if (strlen($contrasena_ingresada) < 8) {
        echo "<script>
                alert('La contraseña debe tener un mínimo de 8 caracteres.');
                window.location.href = '../index.php';
              </script>";
        exit();
    }

    $bd = new BaseDatos();
    $conexion = $bd->obtenerConexion();

    if ($conexion) {
        // Consultar usuario de forma segura con sentencia preparada
        $query = "SELECT * FROM usuarios WHERE usuario = :usuario LIMIT 1";
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(":usuario", $usuario_ingresado);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            // Validar la contraseña ingresada con la almacenada
            if ($contrasena_ingresada === $fila['contrasena']) {
                $_SESSION['token_seguridad'] = bin2hex(random_bytes(16));
                $_SESSION['usuario_activo'] = $fila['usuario'];

                $primera_letra = strtolower(substr($fila['usuario'], 0, 1));

                // Asignar el rol según la letra inicial
                if ($primera_letra === 'a') {
                    $_SESSION['rol'] = 'admin';
                } elseif ($primera_letra === 'c') {
                    $_SESSION['rol'] = 'chef';
                } elseif ($primera_letra === 'j' || $primera_letra === 'm') {
                    $_SESSION['rol'] = 'mesero';
                } else {
                    echo "<script>
                            alert('Este usuario no tiene un rol asignado en el sistema');
                            window.location.href = '../index.php';
                          </script>";
                    exit();
                }

                // Redireccionar al dashboard correspondiente
                switch ($_SESSION['rol']) {
                    case 'admin':
                        header("Location: ../vistas/admin/admin.php");
                        break;
                    case 'chef':
                        header("Location: ../vistas/cocina/cocina.php");
                        break;
                    case 'mesero':
                        header("Location: ../vistas/caja/caja.php");
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