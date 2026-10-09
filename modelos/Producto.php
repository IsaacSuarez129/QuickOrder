<?php

require_once __DIR__ . '/../configuracion/conexion_bd.php';

class Producto {
    private $pdo;

    public function __construct() {
        $db = new BaseDatos();
        $this->pdo = $db->obtenerConexion();
    }

    // Listar todos los productos para el catálogo del admin/mesero
    public function obtenerTodos() {
        $sql = "SELECT id, nombre, descripcion, precio, categoria, disponible FROM productos ORDER BY id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Registrar nuevo producto
    public function registrar($nombre, $descripcion, $precio, $categoria, $disponible) {
        $sql = "INSERT INTO productos (nombre, descripcion, precio, categoria, disponible)
                VALUES (:nombre, :descripcion, :precio, :categoria, :disponible)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nombre'      => $nombre,
            ':descripcion' => $descripcion,
            ':precio'      => $precio,
            ':categoria'   => $categoria,
            ':disponible'  => $disponible
        ]);
    }

    // Actualizar producto existente
    public function actualizar($id, $nombre, $descripcion, $precio, $categoria, $disponible) {
        $sql = "UPDATE productos
                SET nombre = :nombre, descripcion = :descripcion, precio = :precio,
                    categoria = :categoria, disponible = :disponible
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id'          => $id,
            ':nombre'      => $nombre,
            ':descripcion' => $descripcion,
            ':precio'      => $precio,
            ':categoria'   => $categoria,
            ':disponible'  => $disponible
        ]);
    }

    // Alternar disponibilidad (1 a 0, o 0 a 1)
    public function alternarDisponibilidad($id, $estadoActual) {
        $nuevoEstado = ($estadoActual == 1) ? 0 : 1;
        $sql = "UPDATE productos SET disponible = :disponible WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':disponible' => $nuevoEstado,
            ':id'         => $id
        ]);
    }
}