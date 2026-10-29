<?php
/**
 * Clase Database
 * Encargada de gestionar la conexión a la base de datos MySQL
 * utilizando PDO (PHP Data Objects) para mayor seguridad.
 *
 * Revisado 2026-10-29: si la conexión falla, responde JSON y detiene la ejecución
 * en vez de imprimir el error y devolver null.
 */
class Database {
    // Parámetros de conexión
    private $host = "localhost";
    private $db_name = "auth_api";
    private $username = "root";       // Cambiar por tu usuario
    private $password = "";           // Cambiar por tu contraseña
    private $conn;

    /**
     * Método para obtener la conexión a la BD
     * @return PDO Objeto de conexión
     */
    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            // Configurar PDO para lanzar excepciones en caso de error
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            // Sentencias preparadas reales en el servidor, no simuladas en PHP
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $e) {
            // El detalle va al log del servidor, no a la respuesta
            error_log('Conexión a base de datos fallida: ' . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                "estado" => "error",
                "mensaje" => "No se pudo conectar con la base de datos"
            ]);
            exit;   // Sin este exit, el código sigue con la conexión en null
        }
        return $this->conn;
    }
}
?>
