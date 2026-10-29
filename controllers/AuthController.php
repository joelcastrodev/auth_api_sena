<?php
// Incluir archivos necesarios
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

/**
 * Clase AuthController
 * Controlador que maneja la lógica de negocio para
 * el registro e inicio de sesión de usuarios.
 *
 * Revisado 2026-10-29: usuario duplicado devuelve 409 en vez de romper con un
 * error fatal, y la validación ya no trata el string "0" como vacío.
 */
class AuthController {
    private $conn;
    private $user;

    public function __construct() {
        // Obtener conexión a la BD
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->user = new User($this->conn);
    }

    /**
     * Método para registrar un nuevo usuario
     * Endpoint: POST /register
     */
    public function registrar() {
        // Obtener datos enviados en formato JSON
        $data = json_decode(file_get_contents("php://input"));

        // Validar que los datos no estén vacíos.
        // Se usa ?? '' porque empty('0') devuelve true y un usuario "0" es válido.
        $usuario  = $data->usuario  ?? '';
        $password = $data->password ?? '';

        if (is_string($usuario) && is_string($password) && $usuario !== '' && $password !== '') {

            $this->user->usuario = $usuario;
            $this->user->password = $password;

            // Intentar registrar el usuario
            if($this->user->registrar()) {
                http_response_code(201); // 201 Created
                echo json_encode([
                    "estado" => "exitoso",
                    "mensaje" => "Usuario registrado correctamente"
                ]);
            } elseif($this->user->ultimoError === 'duplicado') {
                http_response_code(409); // 409 Conflict
                echo json_encode([
                    "estado" => "error",
                    "mensaje" => "El usuario ya existe"
                ]);
            } else {
                http_response_code(500); // Error del servidor
                echo json_encode([
                    "estado" => "error",
                    "mensaje" => "No se pudo registrar el usuario"
                ]);
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode([
                "estado" => "error",
                "mensaje" => "Datos incompletos. Se requiere usuario y contraseña"
            ]);
        }
    }

    /**
     * Método para iniciar sesión
     * Endpoint: POST /login
     */
    public function login() {
        // Obtener datos enviados en formato JSON
        $data = json_decode(file_get_contents("php://input"));

        // Validar que los datos no estén vacíos
        $usuario  = $data->usuario  ?? '';
        $password = $data->password ?? '';

        if (is_string($usuario) && is_string($password) && $usuario !== '' && $password !== '') {

            $this->user->usuario = $usuario;
            $usuario_db = $this->user->buscarPorUsuario();

            // Verificar si el usuario existe y la contraseña es correcta
            if($usuario_db && password_verify($password, $usuario_db['password'])) {
                http_response_code(200); // OK
                echo json_encode([
                    "estado" => "exitoso",
                    "mensaje" => "Autenticación satisfactoria",
                    "usuario" => $usuario_db['usuario']
                ]);
            } else {
                http_response_code(401); // Unauthorized
                echo json_encode([
                    "estado" => "error",
                    "mensaje" => "Error en la autenticación. Usuario o contraseña incorrectos"
                ]);
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode([
                "estado" => "error",
                "mensaje" => "Datos incompletos. Se requiere usuario y contraseña"
            ]);
        }
    }
}
?>
