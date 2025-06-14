<?php

require_once 'ReservasManager.php';

class ControladorCatalogo
{
    private $reservasManager;
    public $usuarioLogueado;
    public $nombreUsuario;
    public $recursos;
    public $errorDB;

    public function __construct()
    {
        session_start();

        $this->usuarioLogueado = isset($_SESSION['usuario_id']);
        $this->nombreUsuario = $this->usuarioLogueado ? $_SESSION['usuario_nombre'] : '';
        $usuario_id = $this->usuarioLogueado ? $_SESSION['usuario_id'] : null;

        $this->reservasManager = new ReservasManager($usuario_id);
        $this->recursos = $this->reservasManager->getRecursos();
        $this->errorDB = $this->reservasManager->getError();
    }
}

// Ejecutar controlador
$controlador = new ControladorCatalogo();
$usuarioLogueado = $controlador->usuarioLogueado;
$nombreUsuario = $controlador->nombreUsuario;
$recursos = $controlador->recursos;
$errorDB = $controlador->errorDB;

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../multimedia/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Catálogo de Recursos - Muros del Nalón</title>
    <meta name="author" content="Alejandro Fernández García"/>
    <meta name="description" content="Catálogo de Recursos Turísticos - Muros del Nalón"/>
    <meta name="keywords" content="recursos, turismo, catálogo"/>      
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
    
    <p>Estás en: <a href="../index.html">Inicio</a> >> <a href="../reservas.php">Reservas</a> >> Catálogo de Recursos</p>
    
    <main>
        <h1>Catálogo de Recursos Turísticos</h1>
          <?php if(!empty($errorDB)): ?>
            <section>
                <p><?php echo $errorDB; ?></p>
                <p>Por favor, inténtelo de nuevo más tarde o contacte con el administrador.</p>
            </section>
        <?php else: ?>
        
        <section>
            <h3>Descubre todos los recursos turísticos disponibles en Muros del Nalón.</h3>

            <?php if($usuarioLogueado): ?>
                <p>Hola, <strong><?php echo htmlspecialchars($nombreUsuario); ?></strong>. 
                Para hacer una reserva, haz clic en el botón "Reservar" junto al recurso que te interese.</p>
            <?php else: ?>
                <p>Para poder realizar reservas, debes <a href="login.php">iniciar sesión</a> o <a href="register.php">registrarte</a>.</p>
            <?php endif; ?>
        </section>
        
        <section>
            <?php if(empty($recursos)): ?>
                <h3>No hay recursos turísticos disponibles en este momento.</h3>
            <?php else: ?>
                <h3>Recursos Disponibles</h3>
                <?php foreach($recursos as $recurso): ?>
                    <article>
                        <h2><?php echo htmlspecialchars($recurso->getNombre()); ?></h2>

                        <p><strong>Descripción:</strong> <?php echo htmlspecialchars($recurso->getDescripcion()); ?></p>
                        
                        <p><strong>Límite de ocupación:</strong> <?php echo htmlspecialchars($recurso->getLimiteOcupacion()); ?> personas</p>
                        
                        <p><strong>Fecha y hora de inicio:</strong> 
                            <?php 
                            $fechaInicio = $recurso->getFechaHoraInicio();
                            echo ($fechaInicio !== null && $fechaInicio !== '') 
                                ? date('d/m/Y H:i', strtotime($fechaInicio)) 
                                : 'No disponible'; 
                            ?>
                        </p>
                        
                        <p><strong>Fecha y hora de fin:</strong> 
                            <?php 
                            $fechaFin = $recurso->getFechaHoraFin();
                            echo ($fechaFin !== null && $fechaFin !== '') 
                                ? date('d/m/Y H:i', strtotime($fechaFin)) 
                                : 'No disponible'; 
                            ?>
                        </p>
                        
                        <p><strong>Precio:</strong> 
                            <?php echo number_format($recurso->getPrecio(), 2, ',', '.'); ?> €
                        </p>
                        
                        <?php if($usuarioLogueado): ?>
                            <p>
                                <a href="confirmar_reserva.php?id=<?php echo $recurso->getId(); ?>">Reservar</a>
                            </p>
                        <?php endif; ?>
                    </article>
                    <hr>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
          <?php endif; ?>
        
        <p><a href="../reservas.php">Volver a la Central de Reservas</a></p>
    </main>

    <footer>
        <p>2025 Turismo de Muros del Nalón</p>
        <p><a href="https://www.uniovi.es">Universidad de Oviedo</a> 
            - <a href="https://www.uniovi.es/estudia/grados/ingenieria/informaticasoftware/-/fof/asignatura/GIISOF01-3-002">Software y Estándares para la Web</a></p>
        <p><a href="https://github.com/alejandrofdzgarcia">Diseñado por Alejandro Fernández García</a></p>
    </footer>
</body>
</html>
