<?php
session_start();
require_once __DIR__ . '/Controlador_seguridad.php';
require_once __DIR__ . '/../configuracion/conexion_bd.php';

header('Content-Type: application/json; charset=utf-8');

$bd = new BaseDatos();
$conexion = $bd->obtenerConexion();

if (!$conexion) {
    echo json_encode(['error' => 'No se pudo conectar a la base de datos']);
    exit();
}

$accion = $_GET['accion'] ?? 'obtener_pendientes';

// 1. Obtener todas las comandas en curso
if ($accion === 'obtener_pendientes') {
    $sql = "SELECT c.id, c.folio, c.tipo_pedido, c.estado, c.enviada_en, m.numero AS mesa
            FROM comandas c
            LEFT JOIN mesas m ON c.mesa_id = m.id
            WHERE c.estado IN ('pendiente', 'en_preparacion')
            ORDER BY c.enviada_en ASC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $comandas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener los platillos de cada comanda
    foreach ($comandas as &$comanda) {
        $sqlDetalle = "SELECT dc.cantidad, dc.observaciones, p.nombre 
                       FROM detalle_comandas dc
                       INNER JOIN productos p ON dc.producto_id = p.id
                       WHERE dc.comanda_id = :comanda_id";
        $stmtDetalle = $conexion->prepare($sqlDetalle);
        $stmtDetalle->execute([':comanda_id' => $comanda['id']]);
        $comanda['platillos'] = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode($comandas);
    exit();
}

// 2. Cambiar estado de una comanda (Start -> en_preparacion / Finish -> listo)
if ($accion === 'actualizar_estado' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    $comandaId = $body['comanda_id'] ?? null;
    $nuevoEstado = $body['nuevo_estado'] ?? '';

    if ($comandaId && in_array($nuevoEstado, ['en_preparacion', 'listo', 'entregado'])) {
        $sql = "UPDATE comandas SET estado = :estado WHERE id = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':estado' => $nuevoEstado,
            ':id' => $comandaId
        ]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Parámetros inválidos']);
    }
    exit();
}