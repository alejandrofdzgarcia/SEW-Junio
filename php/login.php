<?php
session_start();
require_once 'DBManager.php';

class LoginManager {
    private $error = '';
    private $email = '';
    private $pdo;
    
    public function __construct() {
        if (isset($_SESSION['usuario_id'])) {
            header('Location: ../reservas.php');
            exit;
        }
        
        $this->loadSessionData();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processLoginForm();
        }
    }
    
    private function loadSessionData() {
        $this->error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
        if (isset($_SESSION['error'])) {
            unset($_SESSION['error']);
        }
        
        $this->email = isset($_SESSION['form_data']['email']) ? $_SESSION['form_data']['email'] : '';
        if (isset($_SESSION['form_data'])) {
            unset($_SESSION['form_data']);
        }
    }
    
    private function connectToDatabase() {
        try {
            $dsn = 'mysql:host=localhost;dbname=muros_nalon;charset=utf8mb4';
            $usuario_db = 'DBUSER2025';
            $password_db = 'DBPWD2025';

            $opciones = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ];
            
            $this->pdo = new PDO($dsn, $usuario_db, $password_db, $opciones);
            return true;
        } catch (PDOException $e) {
            $this->error = 'Error de conexión a la base de datos: ' . $e->getMessage();
            return false;
        }
    }
    
    private function processLoginForm() {
        $this->email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        if (empty($this->email) || empty($password)) {
            $this->error = 'Todos los campos son obligatorios';
            return;
        }
        
        if (!$this->connectToDatabase()) {
            return;
        }
        
        try {
            $stmt = $this->pdo->prepare("SELECT id, nombre, email, password FROM usuarios WHERE email = ?");
            $stmt->execute([$this->email]);
            $usuario = $stmt->fetch();
            
            if ($usuario && password_verify($password, $usuario['password'])) {
                $this->authenticateUser($usuario);
            } else {
                $this->error = 'Email o contraseña incorrectos';
            }
        } catch (PDOException $e) {
            $this->error = 'Error al iniciar sesión: ' . $e->getMessage();
        }
    }
    
    private function authenticateUser($usuario) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_email'] = $usuario['email'];
        
        header('Location: ../reservas.php');
        exit;
    }
    
    public function getError() {
        return $this->error;
    }
    
    public function getEmail() {
        return $this->email;
    }
}

$loginManager = new LoginManager();
$error = $loginManager->getError();
$email = $loginManager->getEmail();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../multimedia/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login - Muros del Nalón</title>
    <meta name="author" content="Alejandro Fernández García"/>
    <meta name="description" content="Login - Muros del Nalón"/>
    <meta name="keywords" content="login, usuario, reservas"/> 
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css">
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css">
</head>
<body>
    <header>
        <h1><a href="../index.html" title="Inicio Muros del Nalón">Muros del Nalón</a></h1>
        <nav>
            <a href="../index.html" title="Inicio Muros del Nalón">Página principal</a>
            <a href="../gastronomia.html" title="Gastronomía Muros del Nalón">Gastronomía</a>
            <a href="../rutas.html" title="Rutas Muros del Nalón">Rutas</a>
            <a href="../meteorologia.html" title="Meteorología Muros del Nalón">Meteorología</a>
            <a href="../juego.html" title="Juego Muros del Nalón">Juego</a>
            <a href="../reservas.php" title="Reservas Muros del Nalón">Reservas</a>
            <a href="../ayuda.html" title="Ayuda Muros del Nalón">Ayuda</a>
        </nav>
    </header>
    
    <p>Estás en: <a href="../index.html">Inicio</a> >> <a href="../reservas.php">Reservas</a> >> Iniciar Sesión</p>
    
    <main>
        <section>
            <h2>Iniciar Sesión</h2>
              <?php if (!empty($error)): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            
            <form action="login.php" method="POST">
                <fieldset>
                    <legend>Datos de acceso</legend>
                      <p>
                        <label>Correo Electrónico:
                            <input type="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
                        </label>
                    </p>
                    
                    <p>
                        <label>Contraseña:
                            <input type="password" name="password" required>
                        </label>
                    </p>
                    
                    <button type="submit">Iniciar Sesión</button>
                </fieldset>
            </form>
            
            <p>¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a></p>
        </section>
    </main>

    <footer>
        <p>2025 Turismo de Muros del Nalón</p>
        <p><a href="https://www.uniovi.es">Universidad de Oviedo</a> 
            - <a href="https://www.uniovi.es/estudia/grados/ingenieria/informaticasoftware/-/fof/asignatura/GIISOF01-3-002">Software y Estándares para la Web</a></p>
        <p><a href="https://github.com/alejandrofdzgarcia">Diseñado por Alejandro Fernández García</a></p>
    </footer>
</body>
</html>