<?php

require_once 'UserManager.php';

class ControladorLogin
{
    private $userManager;
    public $email = '';
    public $error = '';

    public function __construct()
    {
        session_start();
        $this->userManager = new UserManager();

        if ($this->userManager->hayUsuarioLogueado()) {
            header('Location: ../reservas.php');
            exit;
        }

        $this->cargarDatosDeSesion();
    }

    private function cargarDatosDeSesion()
    {
        if (isset($_SESSION['error'])) {
            $this->error = $_SESSION['error'];
            unset($_SESSION['error']);
        }

        if (isset($_SESSION['form_data'])) {
            $this->email = $_SESSION['form_data']['email'] ?? '';
            unset($_SESSION['form_data']);
        }
    }

    public function procesarFormulario()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if ($this->userManager->iniciarSesion($this->email, $password)) {
                header('Location: ../reservas.php');
                exit;
            } else {
                $this->error = $this->userManager->getError();
                $formData = $this->userManager->getFormData();
                $this->email = $formData['email'] ?? '';
            }
        }
    }
}

// Ejecutar controlador
$controlador = new ControladorLogin();
$controlador->procesarFormulario();
$error = $controlador->error;
$email = $controlador->email;
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
                <article>
                    <h2>Datos de acceso</h2>
                    <p>
                        <label for="email">Correo Electrónico:</label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
                    </p>
                    
                    <p>
                        <label for="password">Contraseña:</label>
                        <input type="password" id="password" name="password" required>
                    </p>
                    
                    <button type="submit">Iniciar Sesión</button>
                </article>
            </form>
            
            <p>¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a></p>
        </section>
    </main>

    <footer>
        <p>2025 Turismo de Muros del Nalón</p>
        <p><a href="https://www.uniovi.es">Universidad de Oviedo</a> - 
        <a href="https://www.uniovi.es/estudia/grados/ingenieria/informaticasoftware/-/fof/asignatura/GIISOF01-3-002">
            Software y Estándares para la Web</a></p>
        <p><a href="https://github.com/alejandrofdzgarcia">Diseñado por Alejandro Fernández García</a></p>
    </footer>
</body>
</html>
