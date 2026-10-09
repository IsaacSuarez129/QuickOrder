<?php

// 1. Indicar que la respuesta siempre será en formato JSON
header('Content-Type: application/json; charset=utf-8');

// Iniciar sesión para control de acceso (cuando implementes roles de usuario)
session_start();

// Cuando se requiera restringir acceso a solo ciertos roles, descomentar estas líneas:
// if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'mesero'])) {
//     http_response_code(403);
//     echo json_encode(['error' => 'Acceso denegado. No autorizado.']);
//     exit();
// }

// 2. Importar el modelo Producto
require_once __DIR__ . '/../modelos/Producto.php';

try {
    $productoModelo = new Producto();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al conectar con el modelo o la BD: ' . $e->getMessage()]);
    exit();
}

// 3. Obtener la acción solicitada por el frontend
$accion = $_REQUEST['accion'] ?? '';

switch ($accion) {

    // RF-06: Obtener todos los productos para la tabla
    case 'listar':
        try {
            $productos = $productoModelo->obtenerTodos();
            echo json_encode($productos);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al listar productos: ' . $e->getMessage()]);
        }
        break;

    // RF-02 y RF-03: Registrar nuevo o actualizar existente
    case 'guardar':
        // Leer el cuerpo JSON enviado desde fetch
        $cuerpo = file_get_contents('php://input');
        $datos = json_decode($cuerpo, true);

        if (!$datos) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos no válidos o JSON mal formado']);
            exit();
        }

        $id          = !empty($datos['id']) ? intval($datos['id']) : null;
        $nombre      = trim($datos['nombre'] ?? '');
        $descripcion = trim($datos['descripcion'] ?? '');
        $categoria   = trim($datos['categoria'] ?? '');
        $precio      = filter_var($datos['precio'] ?? null, FILTER_VALIDATE_FLOAT);
        $disponible  = !empty($datos['disponible']) ? 1 : 0;

        // RF-07: Validaciones obligatorias de campos y precio positivo
        if (empty($nombre) || empty($categoria) || $precio === false || $precio <= 0) {
            http_response_code(422);
            echo json_encode(['error' => 'Verifica los campos obligatorios: nombre, categoría y precio válido (> 0)']);
            exit();
        }

        try {
            if ($id) {
                // Editar producto existente
                $resultado = $productoModelo->actualizar($id, $nombre, $descripcion, $precio, $categoria, $disponible);
            } else {
                // Registrar nuevo producto
                $resultado = $productoModelo->registrar($nombre, $descripcion, $precio, $categoria, $disponible);
            }

            echo json_encode(['exito' => (bool)$resultado]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al guardar en la base de datos: ' . $e->getMessage()]);
        }
        break;

    // RF-04 y RF-05: Alternar disponibilidad (productos activos / de temporada)
    case 'toggle':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $estadoActual = filter_input(INPUT_GET, 'estado', FILTER_VALIDATE_INT);

        if ($id === false || $id === null || $estadoActual === false || $estadoActual === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetros inválidos para cambiar disponibilidad']);
            exit();
        }

        try {
            $resultado = $productoModelo->alternarDisponibilidad($id, $estadoActual);
            echo json_encode(['exito' => (bool)$resultado]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar disponibilidad: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción no especificada o no válida']);
        break;
}