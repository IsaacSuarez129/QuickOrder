
<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../configuracion/conexion_bd.php';


/** Valida credenciales administrativas usando el esquema de roles actual. */
function validarCredencialesAdministrador(PDO $pdo, $autorizacion): bool
{
    if (!is_array($autorizacion)) {
        return false;
    }

    $usuario = trim((string) ($autorizacion['usuario'] ?? ''));
    $contrasena = (string) ($autorizacion['contrasena'] ?? '');

    if ($usuario === '' || $contrasena === '') {
        return false;
    }

    $stmt = $pdo->prepare(
        'SELECT usuario, contrasena FROM usuarios WHERE usuario = :usuario LIMIT 1'
    );
    $stmt->execute([':usuario' => $usuario]);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fila) {
        return false;
    }

    // El sistema actual asigna el rol a partir de la primera letra del usuario.
    $esAdministrador = strtolower(substr($fila['usuario'], 0, 1)) === 'a';
    return $esAdministrador && hash_equals((string) $fila['contrasena'], $contrasena);
}

// 1. Verificar sesión y permisos
if (
    !isset($_SESSION['id_usuario'], $_SESSION['rol']) ||
    !in_array($_SESSION['rol'], ['mesero', 'admin'], true)
) {
    http_response_code(403);
    echo json_encode([
        'exito' => false,
        'error' => 'No tienes permiso para acceder a este recurso'
    ]);
    exit;
}

// 2. Conexión a la base de datos
$db = new BaseDatos();
$pdo = $db->obtenerConexion();

$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? '';

try {

    switch ($accion) {

        // ==========================================
        // LISTAR COMANDAS PARA GESTIÓN DE CAJA
        // ==========================================
        case 'listar':
            if ($metodo !== 'GET') {
                http_response_code(405);
                echo json_encode(['exito' => false, 'error' => 'Método no permitido']);
                exit;
            }

            $stmt = $pdo->query("
                SELECT c.id, c.folio, c.tipo_pedido, c.mesa_id, m.numero AS mesa,
                       c.usuario_id, u.usuario AS mesero, c.fecha_registro,
                       c.enviada_en, c.estado, c.subtotal, c.total
                FROM comandas c
                LEFT JOIN mesas m ON m.id = c.mesa_id
                LEFT JOIN usuarios u ON u.id = c.usuario_id
                ORDER BY c.fecha_registro DESC, c.id DESC
                LIMIT 200
            ");
            $comandas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmtDetalle = $pdo->prepare("
                SELECT dc.id, dc.producto_id, p.nombre, dc.cantidad,
                       dc.precio_unitario, dc.observaciones, dc.subtotal
                FROM detalle_comandas dc
                INNER JOIN productos p ON p.id = dc.producto_id
                WHERE dc.comanda_id = :id
                ORDER BY dc.id ASC
            ");

            foreach ($comandas as &$comanda) {
                $stmtDetalle->execute([':id' => $comanda['id']]);
                $comanda['productos'] = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);
            }
            unset($comanda);

            echo json_encode(['exito' => true, 'comandas' => $comandas]);
            break;

        // ==========================================
        // LISTAR MESAS OCUPADAS PARA LIBERACIÓN MANUAL
        // ==========================================
        case 'mesas_ocupadas':
            if ($metodo !== 'GET') {
                http_response_code(405);
                echo json_encode(['exito' => false, 'error' => 'Método no permitido']);
                exit;
            }

            $stmtMesas = $pdo->query("
                SELECT m.id, m.numero, m.capacidad, m.estado,
                       (SELECT COUNT(*) FROM comandas c
                        WHERE c.mesa_id = m.id
                          AND c.estado IN ('Pendiente', 'En preparación', 'Listo')) AS comandas_activas
                FROM mesas m
                WHERE m.estado = 'Ocupada'
                ORDER BY m.numero ASC
            
            ");
            echo json_encode(['exito' => true, 'mesas' => $stmtMesas->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        // ==========================================
        // LIBERAR MESA MANUALMENTE
        // ==========================================
        case 'liberar_mesa':
            if ($metodo !== 'POST') {
                http_response_code(405);
                echo json_encode(['exito' => false, 'error' => 'Método no permitido']);
                exit;
            }

            $datosMesa = json_decode(file_get_contents('php://input'), true);
            $mesaId = filter_var($datosMesa['mesa_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$mesaId || $mesaId < 1) {
                http_response_code(400);
                echo json_encode(['exito' => false, 'error' => 'ID de mesa inválido']);
                exit;
            }

            $pdo->beginTransaction();
            $stmtMesa = $pdo->prepare("SELECT id, numero, estado FROM mesas WHERE id = :id FOR UPDATE");
            $stmtMesa->execute([':id' => $mesaId]);
            $mesa = $stmtMesa->fetch(PDO::FETCH_ASSOC);

            if (!$mesa) {
                throw new Exception('La mesa no existe');
            }
            if ($mesa['estado'] !== 'Ocupada') {
                throw new Exception('La mesa no está ocupada');
            }

            $stmtActivas = $pdo->prepare("
                
                    SELECT COUNT(*)
                    FROM comandas
                    WHERE mesa_id = :mesa_id
                    AND estado IN ('Pendiente', 'En preparación')

            ");
            $stmtActivas->execute([':mesa_id' => $mesaId]);
            if ((int) $stmtActivas->fetchColumn() > 0) {
                throw new Exception('No se puede liberar la mesa porque todavía tiene comandas activas. Verifica que hayan sido entregadas o canceladas.');
            }

            $stmtLiberar = $pdo->prepare("UPDATE mesas SET estado = 'Disponible' WHERE id = :id");
            $stmtLiberar->execute([':id' => $mesaId]);
            $pdo->commit();

            echo json_encode(['exito' => true, 'mensaje' => 'Mesa ' . $mesa['numero'] . ' liberada correctamente']);
            break;

        // ==========================================
        // CONSULTAR MESAS DISPONIBLES
        // ==========================================
       

case 'mesas_disponibles':

    $stmt = $pdo->query("
        SELECT id, numero, capacidad, estado
        FROM mesas
        WHERE estado = 'Disponible'
        ORDER BY numero ASC
    ");

    echo json_encode(
        $stmt->fetchAll(PDO::FETCH_ASSOC)
    );

    break;


        // ==========================================
        // CONSULTAR PRODUCTOS ACTIVOS
        // ==========================================
        case 'productos_activos':

            $stmt = $pdo->query("
                SELECT id, nombre, descripcion, precio, categoria
                FROM productos
                WHERE disponible = 1
                ORDER BY categoria ASC, nombre ASC
            ");

            echo json_encode(
                $stmt->fetchAll(PDO::FETCH_ASSOC)
            );
            break;


        // ==========================================
        // CREAR COMANDA
        // ==========================================
        case 'crear':

            if ($metodo !== 'POST') {
                http_response_code(405);
                echo json_encode([
                    'exito' => false,
                    'error' => 'Método no permitido'
                ]);
                exit;
            }

            // Leer los datos enviados
            $datos = json_decode(
                file_get_contents('php://input'),
                true
            );

            if (!is_array($datos)) {
                http_response_code(400);
                echo json_encode([
                    'exito' => false,
                    'error' => 'Los datos enviados no son válidos'
                ]);
                exit;
            }

            // Obtener los datos de la comanda
            $tipoPedido = $datos['tipo_pedido'] ?? 'mesa';

            $mesaId = !empty($datos['mesa_id'])
                ? (int) $datos['mesa_id']
                : null;

            $items = $datos['productos'] ?? [];

            $usuarioId = (int) $_SESSION['id_usuario'];

            // Validar el tipo de pedido
            if (!in_array(
                $tipoPedido,
                ['mesa', 'para_llevar'],
                true
            )) {
                http_response_code(400);
                echo json_encode([
                    'exito' => false,
                    'error' => 'El tipo de pedido no es válido'
                ]);
                exit;
            }

            // Validar la mesa seleccionada
            if (
                $tipoPedido === 'mesa' &&
                (!$mesaId || $mesaId <= 0)
            ) {
                http_response_code(400);
                echo json_encode([
                    'exito' => false,
                    'error' => 'Debe seleccionar una mesa para pedidos en salón'
                ]);
                exit;
            }

            // Los pedidos para llevar no utilizan mesa
            if ($tipoPedido === 'para_llevar') {
                $mesaId = null;
            }

            // Validar los productos recibidos
            if (!is_array($items) || empty($items)) {
                http_response_code(400);
                echo json_encode([
                    'exito' => false,
                    'error' => 'Debe incluir al menos un producto'
                ]);
                exit;
            }

            $pdo->beginTransaction();

            // Verificar y bloquear la mesa durante la transacción
            if ($tipoPedido === 'mesa') {

                $stmtMesa = $pdo->prepare("
                    SELECT id
                    FROM mesas
                    WHERE id = :mesa_id
                      AND estado = 'Disponible'
                    FOR UPDATE
                ");

                $stmtMesa->execute([
                    ':mesa_id' => $mesaId
                ]);

                if (!$stmtMesa->fetch(PDO::FETCH_ASSOC)) {
                    throw new Exception(
                        'La mesa seleccionada no existe o ya no está disponible'
                    );
                }
            }

            // Generar folio de la comanda
            $folio = 'CMD-' .
                date('Ymd') . '-' .
                strtoupper(bin2hex(random_bytes(4)));

            // Registrar la cabecera de la comanda
            $stmtComanda = $pdo->prepare("
                INSERT INTO comandas (
                    folio,
                    tipo_pedido,
                    mesa_id,
                    usuario_id,
                    fecha_registro,
                    enviada_en,
                    estado,
                    subtotal,
                    total
                )
                VALUES (
                    :folio,
                    :tipo_pedido,
                    :mesa_id,
                    :usuario_id,
                    NOW(),
                    NOW(),
                    'Pendiente',
                    0.00,
                    0.00
                )
            ");

            $stmtComanda->execute([
                ':folio' => $folio,
                ':tipo_pedido' => $tipoPedido,
                ':mesa_id' => $mesaId,
                ':usuario_id' => $usuarioId
            ]);

            $comandaId = $pdo->lastInsertId();

            // Consultas para validar precios e insertar detalles
            $stmtPrecio = $pdo->prepare("
                SELECT precio
                FROM productos
                WHERE id = :id
                  AND disponible = 1
            ");

            $stmtDetalle = $pdo->prepare("
                INSERT INTO detalle_comandas (
                    comanda_id,
                    producto_id,
                    cantidad,
                    precio_unitario,
                    observaciones,
                    subtotal
                )
                VALUES (
                    :comanda_id,
                    :producto_id,
                    :cantidad,
                    :precio_unitario,
                    :observaciones,
                    :subtotal
                )
            ");

            $totalAcumulado = 0.00;

            foreach ($items as $item) {

                if (!is_array($item)) {
                    throw new Exception(
                        'Uno de los productos enviados no es válido'
                    );
                }

                $prodId = (int) ($item['producto_id'] ?? 0);
                $cantidad = (int) ($item['cantidad'] ?? 0);

                $obs = $item['observaciones'] ?? '';

                if (!is_string($obs)) {
                    throw new Exception(
                        'Las observaciones de un producto no son válidas'
                    );
                }

                $obs = trim($obs);

                if (
                    $prodId <= 0 ||
                    $cantidad <= 0 ||
                    strlen($obs) > 500
                ) {
                    throw new Exception(
                        'El producto, la cantidad o las observaciones no son válidos'
                    );
                }

                // Consultar el precio directamente en la base de datos
                $stmtPrecio->execute([
                    ':id' => $prodId
                ]);

                $producto = $stmtPrecio->fetch(PDO::FETCH_ASSOC);

                if (!$producto) {
                    throw new Exception(
                        "El producto con ID {$prodId} no existe o no está disponible"
                    );
                }

                $precioUnitario = (float) $producto['precio'];
                $subtotalItem = round(
                    $precioUnitario * $cantidad,
                    2
                );

                $totalAcumulado = round(
                    $totalAcumulado + $subtotalItem,
                    2
                );

                // Insertar el producto en el detalle
                $stmtDetalle->execute([
                    ':comanda_id' => $comandaId,
                    ':producto_id' => $prodId,
                    ':cantidad' => $cantidad,
                    ':precio_unitario' => $precioUnitario,
                    ':observaciones' => $obs !== '' ? $obs : null,
                    ':subtotal' => $subtotalItem
                ]);
            }

            // Actualizar los totales
            $stmtTotal = $pdo->prepare("
                UPDATE comandas
                SET subtotal = :subtotal,
                    total = :total
                WHERE id = :id
            ");

            $stmtTotal->execute([
                ':subtotal' => $totalAcumulado,
                ':total' => $totalAcumulado,
                ':id' => $comandaId
            ]);

            // Ocupar la mesa únicamente para pedidos en salón
            if ($tipoPedido === 'mesa') {

                $stmtOcupar = $pdo->prepare("
                    UPDATE mesas
                    SET estado = 'Ocupada'
                    WHERE id = :mesa_id
                      AND estado = 'Disponible'
                ");

                $stmtOcupar->execute([
                    ':mesa_id' => $mesaId
                ]);

                if ($stmtOcupar->rowCount() !== 1) {
                    throw new Exception(
                        'No fue posible ocupar la mesa seleccionada'
                    );
                }
            }

            $pdo->commit();

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Comanda registrada con éxito',
                'comanda_id' => $comandaId,
                'folio' => $folio,
                'total' => $totalAcumulado
            ]);

            break;


        // ==========================================
        // EDITAR COMANDA
        // ==========================================
        case 'editar':

            if ($metodo !== 'POST') {
                http_response_code(405);
                echo json_encode([
                    'exito' => false,
                    'error' => 'Método no permitido'
                ]);
                exit;
            }

            $datos = json_decode(
                file_get_contents('php://input'),
                true
            );

            if (!is_array($datos)) {
                http_response_code(400);
                echo json_encode([
                    'exito' => false,
                    'error' => 'Los datos enviados no son válidos'
                ]);
                exit;
            }

            $comandaId = (int) ($datos['comanda_id'] ?? 0);
            $items = $datos['productos'] ?? [];

            if (
                $comandaId <= 0 ||
                !is_array($items) ||
                empty($items)
            ) {
                http_response_code(400);
                echo json_encode([
                    'exito' => false,
                    'error' => 'ID de comanda y lista de productos requeridos'
                ]);
                exit;
            }

            $pdo->beginTransaction();

            // Comprobar que la comanda exista
           
 // Comprobar que la comanda exista y esté pendiente
$stmtExiste = $pdo->prepare("
    SELECT id, estado
    FROM comandas
    WHERE id = :id
    FOR UPDATE
");

$stmtExiste->execute([
    ':id' => $comandaId
]);

$comanda = $stmtExiste->fetch(PDO::FETCH_ASSOC);

if (!$comanda) {
    throw new Exception(
        'La comanda que intentas editar no existe'
    );
}

if (!in_array($comanda['estado'], ['Pendiente', 'En preparación'], true)) {
    throw new Exception('Solo se pueden editar comandas pendientes o en preparación');
}

if ($comanda['estado'] === 'En preparación') {
    if (!validarCredencialesAdministrador($pdo, $datos['autorizacion_admin'] ?? null)) {
        http_response_code(403);
        throw new Exception('Se requiere autorización válida de un administrador para editar una comanda en preparación');
    }
}


            // Eliminar los detalles anteriores
            $stmtBorrar = $pdo->prepare("
                DELETE FROM detalle_comandas
                WHERE comanda_id = :comanda_id
            ");

            $stmtBorrar->execute([
                ':comanda_id' => $comandaId
            ]);

            $stmtPrecio = $pdo->prepare("
                SELECT precio
                FROM productos
                WHERE id = :id
                  AND disponible = 1
            ");

            $stmtDetalle = $pdo->prepare("
                INSERT INTO detalle_comandas (
                    comanda_id,
                    producto_id,
                    cantidad,
                    precio_unitario,
                    observaciones,
                    subtotal
                )
                VALUES (
                    :comanda_id,
                    :producto_id,
                    :cantidad,
                    :precio_unitario,
                    :observaciones,
                    :subtotal
                )
            ");

            $totalAcumulado = 0.00;

            foreach ($items as $item) {

                if (!is_array($item)) {
                    throw new Exception(
                        'Uno de los productos enviados no es válido'
                    );
                }

                $prodId = (int) ($item['producto_id'] ?? 0);
                $cantidad = (int) ($item['cantidad'] ?? 0);

                $obs = $item['observaciones'] ?? '';

                if (!is_string($obs)) {
                    throw new Exception(
                        'Las observaciones no son válidas'
                    );
                }

                $obs = trim($obs);

                if (
                    $prodId <= 0 ||
                    $cantidad <= 0 ||
                    strlen($obs) > 500
                ) {
                    throw new Exception(
                        'El producto, la cantidad o las observaciones no son válidos'
                    );
                }

                $stmtPrecio->execute([
                    ':id' => $prodId
                ]);

                $producto = $stmtPrecio->fetch(PDO::FETCH_ASSOC);

                if (!$producto) {
                    throw new Exception(
                        "El producto con ID {$prodId} no existe o no está disponible"
                    );
                }

                $precioUnitario = (float) $producto['precio'];

                $subtotalItem = round(
                    $precioUnitario * $cantidad,
                    2
                );

                $totalAcumulado = round(
                    $totalAcumulado + $subtotalItem,
                    2
                );

                $stmtDetalle->execute([
                    ':comanda_id' => $comandaId,
                    ':producto_id' => $prodId,
                    ':cantidad' => $cantidad,
                    ':precio_unitario' => $precioUnitario,
                    ':observaciones' => $obs !== '' ? $obs : null,
                    ':subtotal' => $subtotalItem
                ]);
            }

            // Actualizar el subtotal y el total de la comanda
            $stmtTotal = $pdo->prepare("
                UPDATE comandas
                SET subtotal = :subtotal,
                    total = :total
                WHERE id = :id
            ");

            $stmtTotal->execute([
                ':subtotal' => $totalAcumulado,
                ':total' => $totalAcumulado,
                ':id' => $comandaId
            ]);

            $pdo->commit();

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Comanda actualizada exitosamente',
                'total' => $totalAcumulado
            ]);

            break;


        // ==========================================
        // CANCELAR COMANDA CON AUTORIZACIÓN SI APLICA
        // ==========================================
        case 'cancelar':
            if ($metodo !== 'POST') {
                http_response_code(405);
                echo json_encode(['exito' => false, 'error' => 'Método no permitido']);
                exit;
            }

            $datos = json_decode(file_get_contents('php://input'), true);
            if (!is_array($datos)) {
                http_response_code(400);
                echo json_encode(['exito' => false, 'error' => 'Los datos enviados no son válidos']);
                exit;
            }

            $comandaId = (int) ($datos['comanda_id'] ?? 0);
            if ($comandaId <= 0) {
                http_response_code(400);
                echo json_encode(['exito' => false, 'error' => 'ID de comanda inválido']);
                exit;
            }

            $pdo->beginTransaction();
            $stmtComanda = $pdo->prepare("SELECT id, estado, mesa_id FROM comandas WHERE id = :id FOR UPDATE");
            $stmtComanda->execute([':id' => $comandaId]);
            $comanda = $stmtComanda->fetch(PDO::FETCH_ASSOC);

            if (!$comanda) {
                throw new Exception('La comanda no existe');
            }
            if (!in_array($comanda['estado'], ['Pendiente', 'En preparación'], true)) {
                throw new Exception('Solo se pueden cancelar comandas pendientes o en preparación');
            }
            if ($comanda['estado'] === 'En preparación'
                && !validarCredencialesAdministrador($pdo, $datos['autorizacion_admin'] ?? null)) {
                http_response_code(403);
                throw new Exception('Se requiere autorización válida de un administrador para cancelar una comanda en preparación');
            }

            $stmtCancelar = $pdo->prepare("UPDATE comandas SET estado = 'Cancelado' WHERE id = :id");
            $stmtCancelar->execute([':id' => $comandaId]);

            // Liberar la mesa solo si no quedan otras comandas activas asociadas a ella.
            if (!empty($comanda['mesa_id'])) {
                $stmtActivas = $pdo->prepare("SELECT COUNT(*) FROM comandas WHERE mesa_id = :mesa_id AND id <> :id AND estado IN ('Pendiente', 'En preparación', 'Listo')");
                $stmtActivas->execute([':mesa_id' => $comanda['mesa_id'], ':id' => $comandaId]);
                if ((int) $stmtActivas->fetchColumn() === 0) {
                    $stmtLiberar = $pdo->prepare("UPDATE mesas SET estado = 'Disponible' WHERE id = :mesa_id");
                    $stmtLiberar->execute([':mesa_id' => $comanda['mesa_id']]);
                }
            }

            $pdo->commit();
            echo json_encode(['exito' => true, 'mensaje' => 'Comanda cancelada. Cocina verá el cambio al actualizarse.']);
            break;

        // ==========================================
        // CAMBIAR ESTADO DE LA COMANDA
        // ==========================================
        case 'cambiar_estado':

            $comandaId = (int) ($_GET['id'] ?? 0);
            $nuevoEstado = trim($_GET['estado'] ?? '');

            // Las cancelaciones deben usar la acción POST 'cancelar', que valida permisos.
            $estadosValidos = [
                'Pendiente',
                'En preparación',
                'Listo',
                'Entregado'
            ];

            if (
                $comandaId <= 0 ||
                !in_array($nuevoEstado, $estadosValidos, true)
            ) {
                http_response_code(400);
                echo json_encode([
                    'exito' => false,
                    'error' => 'Comanda o estado inválido'
                ]);
                exit;
            }

            $pdo->beginTransaction();

            // Consultar la comanda y su mesa
            $stmtComanda = $pdo->prepare("
                 SELECT id, mesa_id, estado
                 FROM comandas
                WHERE id = :id
                FOR UPDATE
            ");

            $stmtComanda->execute([
                ':id' => $comandaId
            ]);

            $comanda = $stmtComanda->fetch(PDO::FETCH_ASSOC);

            if (!$comanda) {
                throw new Exception(
                    'La comanda que intentas actualizar no existe'
                );
            }

            
$transicionesPermitidas = [
    'Pendiente' => ['En preparación'],
    'En preparación' => ['Listo'],
    'Listo' => ['Entregado'],
    'Entregado' => [],
    'Cancelado' => []
];

$estadoActual = $comanda['estado'];

if (
    !in_array(
        $nuevoEstado,
        $transicionesPermitidas[$estadoActual] ?? [],
        true
    )
) {
    throw new Exception(
        'No se permite cambiar de '
        . $estadoActual . ' a ' . $nuevoEstado
    );
}


            // Actualizar el estado con el valor exacto del ENUM
            $stmtEstado = $pdo->prepare("
                UPDATE comandas
                SET estado = :estado
                WHERE id = :id
            ");

            $stmtEstado->execute([
                ':estado' => $nuevoEstado,
                ':id' => $comandaId
            ]);

            // Liberar la mesa cuando se cancela la comanda
            if (
                $nuevoEstado === 'Cancelado' &&
                !empty($comanda['mesa_id'])
            ) {
                $stmtLiberar = $pdo->prepare("
                    UPDATE mesas
                    SET estado = 'Disponible'
                    WHERE id = :mesa_id
                ");

                $stmtLiberar->execute([
                    ':mesa_id' => $comanda['mesa_id']
                ]);
            }

            $pdo->commit();

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Estado de la comanda actualizado',
                'estado' => $nuevoEstado
            ]);

            break;


        // ==========================================
        // ACCIÓN NO RECONOCIDA
        // ==========================================
        default:

            http_response_code(400);

            echo json_encode([
                'exito' => false,
                'error' => 'Acción no reconocida'
            ]);

            break;
    }

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Conserva errores HTTP intencionales como 403 o 400.
    if (http_response_code() < 400) {
        http_response_code(500);
    }

    echo json_encode([
        'exito' => false,
        'error' => $e->getMessage()
    ]);
}
