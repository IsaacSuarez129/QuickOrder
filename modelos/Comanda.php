
<?php

require_once __DIR__ . '/../configuracion/conexion_bd.php';

class Comanda
{
    private $conexion;

    public function __construct()
    {
        $baseDatos = new BaseDatos();
        $this->conexion = $baseDatos->obtenerConexion();
    }
}
