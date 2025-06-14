<?php

require_once 'ReservasManager.php';

class ControladorReservas
{
    private $usuario_id;
    private $nombreUsuario;
    private $reservasManager;
    private $recurso_id;
    private $recurso;
    public $presupuestoGenerado = false;
    public $presupuesto = null;
    public $error = '';

    public function __construct()
    {
        session_start();
        $this->verificarAutenticacion();
        $this->usuario_id = $_SESSION['usuario_id'];
        $this->nombreUsuario = $_SESSION['usuario_nombre'];
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
        if (!$this->obtenerRecursoId()) {
            header('Location: recursos_turisticos.php');
            exit();
        }

        $this->recurso = $this->reservasManager->getRecursoPorId($this->recurso_id);

        if (empty($this->recurso)) {
            header('Location: recursos_turisticos.php');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarPost();
        }
    }

    private function obtenerRecursoId()
    {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            return false;
        }

        $this->recurso_id = (int)$_GET['id'];
        return true;
    }

    private function procesarPost()
    {
        if (isset($_POST['generar_presupuesto'])) {
            $this->generarPresupuesto();
        } elseif (isset($_POST['confirmar_reserva']) && isset($_POST['precio_total'])) {
            $this->confirmarReserva();
        }
    }

    private function generarPresupuesto()
    {
        $num_personas = isset($_POST['num_personas']) ? (int)$_POST['num_personas'] : 1;

        if ($this->reservasManager->generarPresupuesto($this->recurso_id, $num_personas)) {
            $this->presupuestoGenerado = true;
            $this->presupuesto = $this->reservasManager->getPresupuesto();
        } else {
            $this->error = $this->reservasManager->getError();
        }
    }

    private function confirmarReserva()
    {
        $num_personas = isset($_POST['num_personas']) ? (int)$_POST['num_personas'] : 1;
        $precio_total = (float)$_POST['precio_total'];

        if ($this->reservasManager->confirmarReserva($this->recurso_id, $num_personas, $precio_total)) {
            $_SESSION['mensaje_exito'] = "¡Reserva realizada con éxito!";
            header('Location: mis_reservas.php');
            exit();
        } else {
            $this->error = $this->reservasManager->getError();
        }
    }

    public function getRecurso()
    {
        return $this->recurso;
    }

    public function getNombreUsuario()
    {
        return $this->nombreUsuario;
    }
}

// Ejecución
$controlador = new ControladorReservas();
$controlador->procesar();

// Extraer las variables del controlador al ámbito global para usarlas en HTML
$recurso = $controlador->getRecurso();
$nombreUsuario = $controlador->getNombreUsuario();
$presupuestoGenerado = $controlador->presupuestoGenerado;
$presupuesto = $controlador->presupuesto;
$error = $controlador->error;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../multimedia/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Confirmar Reserva - Muros del Nalón</title>
    <meta name="author" content="Alejandro Fernández García"/>
    <meta name="description" content="Confirmación de Reserva - Muros del Nalón"/>
    <meta name="keywords" content="reserva, turismo, confirmación"/>      
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
    
    <p>Estás en: <a href="../index.html">Inicio</a> >> <a href="../reservas.php">Reservas</a> >> <a href="recursos_turisticos.php">Catálogo de Recursos</a> >> Confirmar Reserva</p>
    
    <main>
        <h1>Confirmar Reserva</h1>
        
        <?php if (!empty($error)): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        
        <section>
            <p>Hola, <strong><?php echo htmlspecialchars($nombreUsuario); ?></strong>. 
            Por favor, revisa los detalles del recurso y genera un presupuesto.</p>
            
            <section>
                <h2><?php echo htmlspecialchars($recurso->getNombre()); ?></h2>
                
                <p><strong>Descripción:</strong> <?php echo htmlspecialchars($recurso->getDescripcion()); ?></p>
                
                <p><strong>Límite de ocupación:</strong> <?php echo htmlspecialchars($recurso->getLimiteOcupacion()); ?> personas</p>
                
                <p><strong>Fecha y hora de inicio:</strong> 
                    <?php 
                    echo ($recurso->getFechaHoraInicio() !== null && $recurso->getFechaHoraInicio() !== '') 
                        ? date('d/m/Y H:i', strtotime($recurso->getFechaHoraInicio())) 
                        : 'No disponible'; 
                    ?>
                </p>
                
                <p><strong>Fecha y hora de fin:</strong> 
                    <?php 
                    echo ($recurso->getFechaHoraFin() !== null && $recurso->getFechaHoraFin() !== '') 
                        ? date('d/m/Y H:i', strtotime($recurso->getFechaHoraFin())) 
                        : 'No disponible'; 
                    ?>
                </p>
                
                <p><strong>Precio por persona:</strong> 
                    <?php echo number_format($recurso->getPrecio(), 2, ',', '.'); ?> €
                </p>
            </section>
            
            <?php if (!$presupuestoGenerado): ?>
                <!-- Formulario para generar presupuesto -->
                <form action="" method="post">
                    <input type="hidden" name="recurso_id" value="<?php echo $recurso->getId(); ?>">
                    
                    <label for="num_personas">Número de personas:</label>
                    <input type="number" name="num_personas" min="1" 
                           max="<?php echo $recurso->getLimiteOcupacion(); ?>" value="1" required>
                    
                    <p>
                        <button type="submit" name="generar_presupuesto">Generar Presupuesto</button>
                        <a href="recursos_turisticos.php">Cancelar</a>
                    </p>
                </form>
            <?php else: ?>
                <!-- Mostrar presupuesto y formulario para confirmar reserva -->
                <section>
                    <h3>Presupuesto</h3>
                    <p><strong>Recurso:</strong> <?php echo htmlspecialchars($presupuesto['recurso_nombre']); ?></p>
                    <p><strong>Número de personas:</strong> <?php echo htmlspecialchars($presupuesto['numero_personas']); ?></p>
                    <p><strong>Precio por persona:</strong> <?php echo number_format($presupuesto['precio_unitario'], 2, ',', '.'); ?> €</p>
                    <p><strong>Precio total:</strong> <?php echo number_format($presupuesto['precio_total'], 2, ',', '.'); ?> €</p>
                </section>
                
                <form action="" method="post">
                    <input type="hidden" name="recurso_id" value="<?php echo $recurso->getId(); ?>">
                    <input type="hidden" name="num_personas" value="<?php echo $presupuesto['numero_personas']; ?>">
                    <input type="hidden" name="precio_total" value="<?php echo $presupuesto['precio_total']; ?>">
                    
                    <p>
                        <button type="submit" name="confirmar_reserva">Realizar Reserva</button>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $recurso->getId(); ?>">Modificar</a>
                        <a href="recursos_turisticos.php">Cancelar</a>
                    </p>
                </form>
            <?php endif; ?>
        </section>
        
        <section>
            <p><a href="recursos_turisticos.php">Volver al Catálogo de Recursos</a></p>
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