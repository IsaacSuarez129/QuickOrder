// Conexión a la base de datos

<?php
class BaseDatos {
    private $host = "gateway01.us-east-1.prod.aws.tidbcloud.com";
    private $puerto = "4000";
    private $nombre_db = "test"; // Puedes usar 'test' que viene por defecto
    private $usuario = "gRosPjj5C6jQ4Rn.root";
    private $contrasena = "jr87gH0AFqd4d9nX";
    public $conexion;

    public function obtenerConexion() {
        $this->conexion = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->puerto . ";dbname=" . $this->nombre_db . ";charset=utf8mb4";
            
            $opciones = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_SSL_CA => true,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
            ];

            $this->conexion = new PDO($dsn, $this->usuario, $this->contrasena, $opciones);
        } catch(PDOException $e) {
            echo "Error al conectarse a la base de datos";
        }

        return $this->conexion;
    }
}
?>
