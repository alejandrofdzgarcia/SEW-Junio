<?php

require_once 'ReservasManager.php';

class ControladorMisReservas
{
    private $usuario_id;
    private $reservasManager;
    public $reservasActivas = [];
    public $error = '';
    public $mensaje = '';

    public function __construct()
    {
        session_start();
        $this->verificarAutenticacion();
        $this->usuario_id = $_SESSION['usuario_id'];
        $this->reservasManager = new ReservasManager($this->usuario_id);
    }

    private function verificarAutenticacion()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: login.php');
            exit();
        }
    }

    public function procesar()
    {
        $this->mensaje = $_GET['success'] ?? '';
        $this->error = $_GET['error'] ?? '';

        $reservas = $this->reservasManager->obtenerReservasUsuario();

        $this->reservasActivas = array_filter($reservas, function($reserva) {
            return strtolower($reserva->getEstado()) !== 'cancelada';
        });

        if (empty($this->error) && $this->reservasManager->getError()) {
            $this->error = $this->reservasManager->getError();
        }
    }

    public function getRecursoDeReserva($reserva)
    {
        return $this->reservasManager->getRecursoPorId($reserva->getRecursoId());
    }
}

// Ejecución
$controlador = new ControladorMisReservas();
$controlador->procesar();

// Variables para la vista
$reservasActivas = $controlador->reservasActivas;
$mensaje = $controlador->mensaje;
$error = $controlador->error;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../multimedia/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Mis Reservas - Muros del Nalón</title>
    <meta name="author" content="Alejandro Fernández García"/>
    <meta name="description" content="Mis Reservas - Muros del Nalón"/>
    <meta name="keywords" content="reservas, usuario, turismo"/>
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css">
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css">
</head>
<body>
<header>
    <h1><a href="../index.html">Muros del Nalón</a></h1>
    <nav>
        <a href="../index.html">Página principal</a>
        <a href="../gastronomia.html">Gastronomía</a>
        <a href="../rutas.html">Rutas</a>
        <a href="../meteorologia.html">Meteorología</a>
        <a href="../juego.html">Juego</a>
        <a href="../reservas.php">Reservas</a>
        <a href="../ayuda.html">Ayuda</a>
    </nav>
</header>

<p>Estás en: <a href="../index.html">Inicio</a> >> <a href="../reservas.php">Reservas</a> >> Mis Reservas</p>

<main>
    <h1>Mis Reservas</h1>

    <?php if (!empty($mensaje)): ?>
        <p><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <section>
        <h2>Listado de Reservas</h2>

        <?php if (empty($reservasActivas)): ?>
            <section>
                <h3>No tienes reservas activas en este momento.</h3>
                <p><a href="recursos_turisticos.php">Realizar una reserva</a></p>
            </section>
        <?php else: ?>
            <?php foreach ($reservasActivas as $reserva): ?>
                <?php $recurso = $controlador->getRecursoDeReserva($reserva); ?>
                <article>
                    <h3><?php echo htmlspecialchars($reserva->getRecursoNombre()); ?></h3>
                    <p><strong>Fechas:</strong>
                        <?php echo date('d/m/Y H:i', strtotime($recurso->getFechaHoraInicio())); ?> -
                        <?php echo date('d/m/Y H:i', strtotime($recurso->getFechaHoraFin())); ?>
                    </p>
                    <p><strong>Personas:</strong> <?php echo $reserva->getNumeroPersonas(); ?></p>
                    <p><strong>Precio Total:</strong> <?php echo number_format($reserva->getPrecioTotal(), 2); ?> €</p>
                    <p><strong>Estado:</strong> <?php echo htmlspecialchars($reserva->getEstado()); ?></p>
                    <p><strong>Descripción:</strong> <?php echo htmlspecialchars($reserva->getRecursoDescripcion()); ?></p>
                    <section>
                        <h4>Fecha de Reserva:</h4>
                        <p><span><?php echo date('d/m/Y', strtotime($reserva->getFechaReserva())); ?></span></p>
                        <p>
                            <a href="cancelar_reserva.php?id=<?php echo $reserva->getId(); ?>"
                               onclick="return confirm('¿Estás seguro de que deseas cancelar esta reserva?');">
                                Cancelar reserva
                            </a>
                        </p>
                    </section>
                </article>
            <?php endforeach; ?>

            <section>
                <h4>Acciones</h4>
                <p>
                    <a href="recursos_turisticos.php">Realizar otra reserva</a>
                    <a href="../reservas.php">Volver a Reservas</a>
                </p>
            </section>
        <?php endif; ?>
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
