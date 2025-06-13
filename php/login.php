<?php
session_start();
require_once 'UserManager.php';

// Crear instancia del gestor de usuarios
$userManager = new UserManager();

// Si ya hay una sesión activa, redirigir a la página de reservas
if ($userManager->hayUsuarioLogueado()) {
    header('Location: ../reservas.php');
    exit;
}

// Variables para el formulario
$error = '';
$email = '';

// Cargar datos de sesión (si hay error previo)
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['form_data'])) {
    $email = isset($_SESSION['form_data']['email']) ? $_SESSION['form_data']['email'] : '';
    unset($_SESSION['form_data']);
}

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if ($userManager->iniciarSesion($email, $password)) {
        // Login exitoso, redirigir a la página de reservas
        header('Location: ../reservas.php');
        exit;
    } else {
        // Login fallido, obtener error y datos del formulario
        $error = $userManager->getError();
        $formData = $userManager->getFormData();
        $email = isset($formData['email']) ? $formData['email'] : '';
    }
}
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
                </article>
            </form>
            
            <p>¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a></p>
        </section>
    </main>

    <footer>
        <p>2025 Turismo de Muros del Nalón</p>  <p>2025 Turismo de Muros del Nalón</p>
        <p><a href="https://www.uniovi.es">Universidad de Oviedo</a> //www.uniovi.es">Universidad de Oviedo</a> 
            - <a href="https://www.uniovi.es/estudia/grados/ingenieria/informaticasoftware/-/fof/asignatura/GIISOF01-3-002">Software y Estándares para la Web</a></p>informaticasoftware/-/fof/asignatura/GIISOF01-3-002">Software y Estándares para la Web</a></p>
        <p><a href="https://github.com/alejandrofdzgarcia">Diseñado por Alejandro Fernández García</a></p>jandro Fernández García</a></p>
    </footer>
</body>
</html>