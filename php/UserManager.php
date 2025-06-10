<?php
/**
 * Clase para gestionar usuarios (registro, autenticación, etc.)
 * 
 * @author Alejandro Fernández García - UO295813
 * @version 1.1
 */

require_once 'DBManager.php';

class UserManager {
    private $db;
    private $error;
    private $formData;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        $dbManager = new DBManager();
        $this->db = $dbManager->getConnection();
        $this->error = '';
        $this->formData = [];
    }
    
    /**
     * Registra un nuevo usuario en el sistema
     * 
     * @param string $nombre Nombre completo del usuario
     * @param string $email Email del usuario
     * @param string $password Contraseña sin cifrar
     * @param string $password_confirm Confirmación de contraseña
     * @return bool True si el registro es exitoso, False en caso contrario
     */
    public function registrarUsuario($nombre, $email, $password, $password_confirm) {
        // Limpiar datos de entrada
        $nombre = trim($nombre);
        $email = trim($email);
        
        // Validar campos obligatorios
        if (empty($nombre) || empty($email) || empty($password) || empty($password_confirm)) {
            $this->error = 'Todos los campos son obligatorios';
            $this->formData = [
                'nombre' => $nombre,
                'email' => $email
            ];
            return false;
        }
        
        // Validar coincidencia de contraseñas
        if ($password !== $password_confirm) {
            $this->error = 'Las contraseñas no coinciden';
            $this->formData = [
                'nombre' => $nombre,
                'email' => $email
            ];
            return false;
        }
        
        // Validar formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error = 'El formato del email no es válido';
            $this->formData = [
                'nombre' => $nombre
            ];
            return false;
        }
        
        try {
            // Comprobar si el email ya está registrado
            $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $this->error = 'El email ya está registrado';
                $this->formData = [
                    'nombre' => $nombre
                ];
                return false;
            }
            
            // Cifrar contraseña y registrar usuario
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $nombre, $email, $password_hash);
            
            if ($stmt->execute()) {
                // Registro exitoso
                return true;
            } else {
                $this->error = 'Error al registrar el usuario: ' . $this->db->error;
                $this->formData = [
                    'nombre' => $nombre,
                    'email' => $email
                ];
                return false;
            }
        } catch (Exception $e) {
            $this->error = 'Error al registrar el usuario: ' . $e->getMessage();
            $this->formData = [
                'nombre' => $nombre,
                'email' => $email
            ];
            return false;
        }
    }
    
    /**
     * Autentica a un usuario en el sistema
     * 
     * @param string $email Email del usuario
     * @param string $password Contraseña sin cifrar
     * @return bool True si la autenticación es exitosa, False en caso contrario
     */
    public function iniciarSesion($email, $password) {
        // Limpiar datos de entrada
        $email = trim($email);
        
        // Validar campos obligatorios
        if (empty($email) || empty($password)) {
            $this->error = 'Todos los campos son obligatorios';
            $this->formData = [
                'email' => $email
            ];
            return false;
        }
        
        try {
            // Buscar el usuario por email
            $stmt = $this->db->prepare("SELECT id, nombre, email, password FROM usuarios WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $usuario = $result->fetch_assoc();
            
            // Verificar si el usuario existe y la contraseña es correcta
            if ($usuario && password_verify($password, $usuario['password'])) {
                // Guardar datos en sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $usuario['email'];
                
                return true;
            } else {
                $this->error = 'Email o contraseña incorrectos';
                $this->formData = [
                    'email' => $email
                ];
                return false;
            }
        } catch (Exception $e) {
            $this->error = 'Error al iniciar sesión: ' . $e->getMessage();
            $this->formData = [
                'email' => $email
            ];
            return false;
        }
    }
    
    /**
     * Verifica si un usuario ya ha iniciado sesión
     * 
     * @return bool True si hay una sesión activa, False en caso contrario
     */
    public function hayUsuarioLogueado() {
        return isset($_SESSION['usuario_id']);
    }
    
    /**
     * Cierra la sesión del usuario actual
     * 
     * @return bool True si el cierre de sesión es exitoso
     */
    public function cerrarSesion() {
        // Limpiar todas las variables de sesión
        $_SESSION = [];
        
        // Si se utilizan cookies de sesión, eliminarlas
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }
        
        // Destruir la sesión
        session_destroy();
        
        return true;
    }
    
    /**
     * Obtiene el ID del último usuario registrado
     * 
     * @return int ID del último usuario insertado
     */
    public function getLastInsertId() {
        return $this->db->insert_id;
    }
    
    /**
     * Obtiene el mensaje de error
     * 
     * @return string Mensaje de error
     */
    public function getError() {
        return $this->error;
    }
    
    /**
     * Obtiene los datos del formulario para persistencia
     * 
     * @return array Datos del formulario
     */
    public function getFormData() {
        return $this->formData;
    }
}