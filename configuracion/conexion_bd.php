// Conexión a la base de datos

<?php
class BaseDatos {
    private $host = "127.0.0.1";
    private $nombre_db = "quickorder_db";
    private $usuario = "root";
    private $contrasena = "TU_CONTRASEÑA_AQUI"; // Pon tu contraseña aquí
    public $conexion;

    public function obtenerConexion() {
        $this->conexion = null;

        try {
            $this->conexion = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->nombre_db . ";charset=utf8mb4", $this->usuario, $this->contrasena);
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Error al conectarse a la base de datos";
        }

        return $this->conexion;
    }
}
?>