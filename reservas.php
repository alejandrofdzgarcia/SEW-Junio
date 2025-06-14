<?php

    require_once 'php/DBManager.php';
    require_once 'php/UserManager.php';

    class ControladorReservas
    {
        private $dbManager;
        public $mensaje = '';
        public $error = '';
        public $errorDB = '';
        public $usuarioLogueado = false;
        public $nombreUsuario = '';
        public $esAdministrador = false;

        public function __construct()
        {
            session_start();

            $this->dbManager = new DBManager();
            new UserManager();

            $this->mensaje = $_GET['mensaje'] ?? '';
            $this->error = $_GET['error'] ?? '';

            $this->inicializarBD();

            $this->usuarioLogueado = isset($_SESSION['usuario_id']);
            $this->nombreUsuario = $this->usuarioLogueado ? $_SESSION['usuario_nombre'] : '';
            $this->esAdministrador = $this->usuarioLogueado && !empty($_SESSION['es_admin']);
        }

        private function inicializarBD()
        {
            try {
                $this->dbManager->createDatabase();
                $this->dbManager->importFromCSV("php/recursos_turisticos.csv");
                $conn = $this->dbManager->getConnection();
                
                if ($conn) {
                    $this->errorDB = '';
                }
            } catch (Exception $e) {
                $this->errorDB = "Error de conexión a la base de datos: " . $e->getMessage();
            }
        }
    }

    $controlador = new ControladorReservas();
    $mensaje = $controlador->mensaje;
    $error = $controlador->error;
    $errorDB = $controlador->errorDB;
    $usuarioLogueado = $controlador->usuarioLogueado;
    $nombreUsuario = $controlador->nombreUsuario;
    $esAdministrador = $controlador->esAdministrador;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="multimedia/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Reservas - Muros del Nalón</title>
    <meta name="author" content="Alejandro Fernández García"/>
    <meta name="description" content="Reservas - Muros del Nalón"/>
    <meta name="keywords" content="reservas, central"/>      
    <link rel="stylesheet" type="text/css" href="estilo/estilo.css">
    <link rel="stylesheet" type="text/css" href="estilo/layout.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header>
        <h1><a href="index.html" title="Inicio Muros del Nalón">Muros del Nalón</a></h1>
        <nav>
            <a href="index.html" title="Inicio Muros del Nalón">Página principal</a>
            <a href="gastronomia.html" title="Gastronomía Muros del Nalón">Gastronomía</a>
            <a href="rutas.html" title="Rutas Muros del Nalón">Rutas</a>
            <a href="meteorologia.html" title="Meteorología Muros del Nalón">Meteorología</a>
            <a href="juego.html" title="Juego Muros del Nalón">Juego</a>
            <a href="reservas.php" title="Reservas Muros del Nalón" class="active">Reservas</a>
            <a href="ayuda.html" title="Ayuda Muros del Nalón">Ayuda</a>
        </nav>
    </header>
    
    <p>Estás en: <a href="index.html">Inicio</a> >> Reservas</p>
    
    <main>
        <h1>Central de Reservas Turísticas</h1>
        
        <?php if (!empty($mensaje)): ?>
            <section>
                <h3>Información</h3>
                <p><?php echo htmlspecialchars($mensaje); ?></p>
            </section>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <section>
                <h3>Error</h3>
                <p><?php echo htmlspecialchars($error); ?></p>
            </section>
        <?php endif; ?>
        
        <?php if(!empty($errorDB)): ?>
            <section>
                <p><strong>Error de Base de Datos:</strong> <?php echo $errorDB; ?></p>
                <p>Por favor, contacte con el administrador del sistema.</p>
            </section>
        <?php endif; ?>
        
        <section>
            <?php if($usuarioLogueado): ?>
                <h3>Bienvenido/a, <strong><?php echo htmlspecialchars($nombreUsuario); ?></strong></h3>
                <form action="php/logout.php" method="POST">
                    <label for="btn-logout">Botón para cerrar sesión</label>
                    <button id="btn-logout" type="submit">Cerrar sesión</button>
                </form>
            <?php else: ?>
                <h3>Inicia sesión para gestionar tus reservas</h3>
                <p>
                    <a href="php/login.php">Iniciar sesión</a>
                    <a href="php/register.php">Registrarse</a>
                </p>
            <?php endif; ?>
        </section>
        <section>
            <h4>Servicios disponibles</h4>
            <?php if($usuarioLogueado): ?>
                
                <article>
                    <h2>Catálogo de Recursos</h2>
                    <p>Explora nuestros recursos turísticos disponibles y realiza una reserva.</p>
                    <p><a href="php/recursos_turisticos.php">Ver recursos</a></p>
                </article>
                
                <article>
                    <h2>Mis Reservas</h2>
                    <p>Consulta y gestiona tus reservas actuales.</p>
                    <p><a href="php/mis_reservas.php">Ver mis reservas</a></p>
                </article>

                <article>
                    <h2>Importar/exportar CSV</h2>
                    <p>Importe o exporte datos en la base de datos.</p>
                    <p><a href="php/importar_exportar_csv.php">Ver importar/exportar CSV</a></p>
                </article>
            <?php else: ?>
                <h4>Debes iniciar sesión</h4>
                <article>
                    <h4>Información de acceso</h4>
                    <p>Para realizar reservas, consultar o cancelarlas, debes iniciar sesión.</p>
                    <p>Si no tienes una cuenta, regístrate para acceder a todos los servicios.</p>
                </article>
            <?php endif; ?>
        </section>
        
        <section>
            <h3>Instrucciones de uso</h3>            
            <article>
                <h2>¿Cómo funciona?</h2>
                <ol>
                    <li>Regístrate en nuestra plataforma o inicia sesión si ya tienes cuenta.</li>
                    <li>Explora el catálogo de recursos turísticos disponibles.</li>
                    <li>Selecciona el recurso que deseas reservar.</li>
                    <li>Revisa el presupuesto generado automáticamente.</li>
                    <li>Confirma tu reserva.</li>
                </ol>
            </article>
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