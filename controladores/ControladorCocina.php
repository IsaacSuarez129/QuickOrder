
<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../configuracion/conexion_bd.php';

// Solo cocina y administrador pueden consultar y actualizar comandas.
if (
    !isset($_SESSION['rol']) ||
    !in_array($_SESSION['rol'], ['chef', 'admin'], true)
) {
    http_response_code(403);
    echo json_encode([
        'exito' => false,
        'error' => 'No tienes permiso para acceder a cocina'
    ]);
    exit;
}

try {
    $bd = new BaseDatos();
    $conexion = $bd->obtenerConexion();

    if (!$conexion) {
        throw new RuntimeException('No se pudo conectar a la base de datos');
    }

    $accion = $_GET['accion'] ?? 'obtener_pendientes';

    // 1. Consultar comandas pendientes y en preparación.
    if ($accion === 'obtener_pendientes' && $_SERVER['REQUEST_METHOD'] === 'GET') {

        $sql = "SELECT
                    c.id,
                    c.folio,
                    c.tipo_pedido,
                    c.estado,
                    c.enviada_en,
                    m.numero AS mesa
                FROM comandas c
                LEFT JOIN mesas m ON c.mesa_id = m.id
                WHERE c.estado IN ('Pendiente', 'En preparación')
                ORDER BY c.enviada_en ASC, c.id ASC";

        $stmt = $conexion->prepare($sql);
        $stmt->execute();

        $comandas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Consultar los productos de cada comanda.
        $sqlDetalle = "SELECT
                           dc.cantidad,
                           dc.observaciones,
                           dc.precio_unitario,
                           dc.subtotal,
                           p.nombre
                       FROM detalle_comandas dc
                       INNER JOIN productos p
                           ON dc.producto_id = p.id
                       WHERE dc.comanda_id = :comanda_id
                       ORDER BY dc.id ASC";

        $stmtDetalle = $conexion->prepare($sqlDetalle);

        foreach ($comandas as &$comanda) {
            $stmtDetalle->execute([
                ':comanda_id' => $comanda['id']
            ]);

            $comanda['platillos'] = $stmtDetalle->fetchAll(
                PDO::FETCH_ASSOC
            );
        }

        unset($comanda);

        echo json_encode([
            'exito' => true,
            'comandas' => $comandas
        ]);

        exit;
    }

    // 2. Actualizar estado mediante POST.
    if ($accion === 'actualizar_estado' && $_SERVER['REQUEST_METHOD'] === 'POST') {

        $body = json_decode(file_get_contents('php://input'), true);

        if (!is_array($body)) {
            http_response_code(400);
            echo json_encode([
                'exito' => false,
                'error' => 'El cuerpo de la solicitud no es válido'
            ]);
            exit;
        }

        $comandaId = filter_var(
            $body['comanda_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        $nuevoEstado = $body['nuevo_estado'] ?? '';

        if (
            !$comandaId ||
            !in_array($nuevoEstado, ['En preparación', 'Listo'], true)
        ) {
            http_response_code(400);
            echo json_encode([
                'exito' => false,
                'error' => 'Los datos enviados no son válidos'
            ]);
            exit;
        }

        $conexion->beginTransaction();

        $stmt = $conexion->prepare(
            "SELECT id, estado
             FROM comandas
             WHERE id = :id
             FOR UPDATE"
        );

        $stmt->execute([':id' => $comandaId]);
        $comanda = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$comanda) {
            $conexion->rollBack();

            http_response_code(404);
            echo json_encode([
                'exito' => false,
                'error' => 'No se encontró la comanda'
            ]);
            exit;
        }

        // Evitar saltarse etapas del proceso.
        $transicionesPermitidas = [
            'Pendiente' => 'En preparación',
            'En preparación' => 'Listo'
        ];

        if (
            !isset($transicionesPermitidas[$comanda['estado']]) ||
            $transicionesPermitidas[$comanda['estado']] !== $nuevoEstado
        ) {
            $conexion->rollBack();

            http_response_code(409);
            echo json_encode([
                'exito' => false,
                'error' => 'La comanda no puede pasar de ' .
                    $comanda['estado'] . ' a ' . $nuevoEstado
            ]);
            exit;
        }

        $stmt = $conexion->prepare(
            "UPDATE comandas
             SET estado = :estado
             WHERE id = :id"
        );

        $stmt->execute([
            ':estado' => $nuevoEstado,
            ':id' => $comandaId
        ]);

        $conexion->commit();

        echo json_encode([
            'exito' => true,
            'mensaje' => 'Estado de la comanda actualizado',
            'estado' => $nuevoEstado
        ]);

        exit;
    }

    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'error' => 'Acción o método no permitido'
    ]);

} catch (Throwable $e) {
    if (isset($conexion) && $conexion->inTransaction()) {
        $conexion->rollBack();
    }

    error_log($e->getMessage());

    http_response_code(500);
    echo json_encode([
        'exito' => false,
        'error' => 'Ocurrió un error al procesar la solicitud'
    ]);
}
