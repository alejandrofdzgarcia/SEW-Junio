<?php

require_once 'UserManager.php';

class ControladorRegistro
{
    private $userManager;
    public $error = '';
    public $nombre = '';
    public $email = '';

    public function __construct()
    {
        session_start();

        // Si ya está logueado, redirige
        if (isset($_SESSION['usuario_id'])) {
            header('Location: ../reservas.php');
            exit;
        }

        $this->userManager = new UserManager();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarFormulario();
        } else {
            $this->cargarDatosPrevios();
        }
    }

    private function procesarFormulario()
    {
        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if ($this->userManager->registrarUsuario($nombre, $email, $password, $password_confirm)) {
            $_SESSION['usuario_id'] = $this->userManager->getLastInsertId();
            $_SESSION['usuario_nombre'] = $nombre;
            $_SESSION['usuario_email'] = $email;

            header('Location: ../reservas.php');
            exit;
        } else {
            $this->error = $this->userManager->getError();
            $formData = $this->userManager->getFormData();
            $this->nombre = $formData['nombre'] ?? '';
            $this->email = $formData['email'] ?? '';
        }
    }

    private function cargarDatosPrevios()
    {
        if (isset($_SESSION['error'])) {
            $this->error = $_SESSION['error'];
            unset($_SESSION['error']);
        }

        if (isset($_SESSION['form_data'])) {
            $this->nombre = $_SESSION['form_data']['nombre'] ?? '';
            $this->email = $_SESSION['form_data']['email'] ?? '';
            unset($_SESSION['form_data']);
        }
    }
}

// Ejecutar controlador
$controlador = new ControladorRegistro();
$error = $controlador->error;
$nombre = $controlador->nombre;
$email = $controlador->email;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../multimedia/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Registro - Muros del Nalón</title>
    <meta name="author" content="Alejandro Fernández García"/>
    <meta name="description" content="Registro de Usuario - Muros del Nalón"/>
    <meta name="keywords" content="registro, usuario, reservas"/>    
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
    
    <p>Estás en: <a href="../index.html">Inicio</a> >> <a href="../reservas.php">Reservas</a> >> Registro</p>
    
    <main>
        <section>
            <h2>Registro de Usuario</h2>
            <?php if (!empty($error)): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <article>
                    <legend>Datos personales</legend>
                    <p>
                        <label>Nombre:
                            <input type="text" name="nombre" required value="<?php echo htmlspecialchars($nombre); ?>">
                        </label>
                    </p>
                    <p>
                        <label>Email:
                            <input type="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
                        </label>
                    </p>
                    <p>
                        <label>Contraseña:
                            <input type="password" name="password" required>
                        </label>
                    </p>
                    <p>
                        <label>Confirmar contraseña:
                            <input type="password" name="password_confirm" required>
                        </label>
                    </p>
                    
                    <button type="submit">Registrar</button>
                </article>
            </form>
            
            <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
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