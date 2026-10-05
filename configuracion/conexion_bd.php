// Conexión a la base de datos

<?php
class BaseDatos {
    private $host = "gateway01.us-east-1.prod.aws.tidbcloud.com";
    private $puerto = "4000";
    private $nombre_db = "test";
    private $usuario = "gRosPjj5C6jQ4Rn.root";
    private $contrasena = "jr87gH0AFqd4d9nX"; // Tu contraseña real
    public $conexion;

    public function obtenerConexion() {
        $this->conexion = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->puerto . ";dbname=" . $this->nombre_db . ";charset=utf8mb4";

            $opciones = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                \Pdo\Mysql::ATTR_SSL_CA => __DIR__ . '/isrgrootx1.pem',
                \Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT => false
            ];

            $this->conexion = new PDO($dsn, $this->usuario, $this->contrasena, $opciones);
        } catch(PDOException $e) {
            die("Error detallado de conexión: " . $e->getMessage());
        }

        return $this->conexion;
    }
}
?>
