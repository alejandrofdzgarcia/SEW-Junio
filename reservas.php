<!DOCTYPE html>
<html lang="es">
<?php
    session_start();
    
    // Incluir la clase DBManager
    require_once 'php/DBManager.php';
    
    // Inicializar el gestor de base de datos
     // Inicializar la base de datos si es necesario
    $dbManager = new DBManager();
    $dbManager->createDatabase();
    $dbManager->importFromCSV("recursos_turisticos", "php/recursos_turisticos.csv");
    
    // Intentar establecer la conexión a la base de datos
    try {
        $db = $dbManager->getConnection();
    } catch (Exception $e) {
        // Si hay un error, mostrar un mensaje
        $errorDB = "Error de conexión a la base de datos: " . $e->getMessage();
    }
    
    // Comprobar si el usuario está logueado
    $usuarioLogueado = isset($_SESSION['usuario_id']);
    $nombreUsuario = $usuarioLogueado ? $_SESSION['usuario_nombre'] : '';
    
    // Comprobar si el usuario es administrador (esto podría venir de la base de datos)
    $esAdministrador = $usuarioLogueado && isset($_SESSION['es_admin']) && $_SESSION['es_admin'];
?>
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
    <link rel="stylesheet" type="text/css" href="estilo/reservas.css">
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
          <?php if(isset($errorDB)): ?>
            <section>
                <p><strong>Error de Base de Datos:</strong> <?php echo $errorDB; ?></p>
                <p>Por favor, contacte con el administrador del sistema.</p>
            </section>
        <?php endif; ?>
        
        <!-- Bienvenida y estado de sesión -->
        <section>
            <?php if($usuarioLogueado): ?>
                <p>Bienvenido/a, <strong><?php echo htmlspecialchars($nombreUsuario); ?></strong></p>
                <p><a href="php/logout.php">Cerrar sesión</a></p>
                  <?php if($esAdministrador): ?>                    <section>
                        <p><strong>Panel de Administración</strong></p>
                        <ul>
                            <li><a href="php/init_database.php">Inicializar Base de Datos</a></li>
                            <li><a href="php/cargar_recursos.php">Cargar Recursos Turísticos desde CSV</a></li>
                            <li><a href="php/gestionar_recursos.php">Gestionar Recursos Turísticos</a></li>
                            <li><a href="php/gestionar_usuarios.php">Gestionar Usuarios</a></li>
                            <li><a href="php/ver_todas_reservas.php">Ver Todas las Reservas</a></li>
                        </ul>
                    </section>
                <?php endif; ?>
            <?php else: ?>
                <p>Inicia sesión para gestionar tus reservas</p>
                <p>
                    <a href="php/login.php">Iniciar sesión</a>
                    <a href="php/register.php">Registrarse</a>
                </p>
            <?php endif; ?>
        </section>
          <!-- Menú principal de la central de reservas -->
        <section>
            <!-- Catálogo de recursos turísticos - accesible a todos -->
            <fieldset>
                <legend>Catálogo de Recursos</legend>
                <p>Explora nuestros recursos turísticos disponibles.</p>
                <p><a href="php/recursos_turisticos.php">Ver recursos</a></p>
            </fieldset>
            
            <?php if($usuarioLogueado): ?>
                <!-- Opciones solo para usuarios registrados -->
                <fieldset>
                    <legend>Hacer una Reserva</legend>
                    <p>Reserva el recurso turístico que más te interese.</p>
                    <p><a href="php/realizar_reserva.php">Reservar ahora</a></p>
                </fieldset>
                
                <fieldset>
                    <legend>Mis Reservas</legend>
                    <p>Consulta y gestiona tus reservas actuales.</p>
                    <p><a href="php/mis_reservas.php">Ver mis reservas</a></p>
                </fieldset>
                
                <fieldset>
                    <legend>Cancelar Reserva</legend>
                    <p>Anula reservas que ya no necesites.</p>
                    <p><a href="php/cancelar_reserva.php">Cancelar reserva</a></p>
                </fieldset>
            <?php else: ?>
                <!-- Mensaje para usuarios no registrados -->
                <fieldset>
                    <legend>Información de acceso</legend>
                    <p>Para realizar reservas, consultar o cancelarlas, debes iniciar sesión.</p>
                    <p>Si no tienes una cuenta, regístrate para acceder a todos los servicios.</p>
                </fieldset>
            <?php endif; ?>
        </section>
        
        <!-- Información adicional sobre las reservas -->
        <section>            
            <fieldset>
                <legend>¿Cómo funciona?</legend>
                <ol>
                    <li>Regístrate en nuestra plataforma o inicia sesión si ya tienes cuenta.</li>
                    <li>Explora el catálogo de recursos turísticos disponibles.</li>
                    <li>Selecciona el recurso que deseas reservar.</li>
                    <li>Revisa el presupuesto generado automáticamente.</li>
                    <li>Confirma tu reserva.</li>
                </ol>
            </fieldset>
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