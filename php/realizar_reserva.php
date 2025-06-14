<?php
/**
 * Formulario para realizar reservas de recursos turísticos
 * Utiliza el paradigma orientado a objetos con ReservasManager
 * 
 * @author Alejandro Fernández García - UO295813
 * @version 2.0
 */

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Incluir la clase ReservasManager
require_once 'ReservasManager.php';

// Inicializar el gestor de reservas con el ID del usuario
$reservasManager = new ReservasManager($_SESSION['usuario_id']);

// Variables para almacenar resultados
$mensaje = '';
$error = '';
$presupuesto = null;

// Procesar formularios POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Generar presupuesto
    if (isset($_POST['generar_presupuesto'])) {
        $recurso_id = (int)$_POST['recurso_id'];
        $numero_personas = (int)$_POST['numero_personas'];
        
        if ($reservasManager->generarPresupuesto($recurso_id, $numero_personas)) {
            $presupuesto = $reservasManager->getPresupuesto();
        } else {
            $error = $reservasManager->getError();
        }
    }
    
    // Confirmar reserva
    if (isset($_POST['confirmar_reserva'])) {
        $recurso_id = (int)$_POST['recurso_id'];
        $numero_personas = (int)$_POST['numero_personas'];
        $precio_total = (float)$_POST['precio_total'];
        
        if ($reservasManager->confirmarReserva($recurso_id, $numero_personas, $precio_total)) {
            $mensaje = $reservasManager->getMensaje();
        } else {
            $error = $reservasManager->getError();
        }
    }
}

// Obtener recursos disponibles
$recursos = $reservasManager->getRecursos();

// Si no hay presupuesto en POST pero hay mensaje en el gestor, actualizar el presupuesto
if (!$presupuesto && $reservasManager->getPresupuesto()) {
    $presupuesto = $reservasManager->getPresupuesto();
}

// Si no hay mensaje o error en POST pero hay en el gestor, actualizar
if (empty($mensaje) && $reservasManager->getMensaje()) {
    $mensaje = $reservasManager->getMensaje();
}

if (empty($error) && $reservasManager->getError()) {
    $error = $reservasManager->getError();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>    
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../multimedia/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Reservar - Muros del Nalón</title>
    <meta name="author" content="Alejandro Fernández García"/>
    <meta name="description" content="Reservar - Muros del Nalón"/>    
    <meta name="keywords" content="reservar, central"/>      
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css">
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function actualizarPrecio() {
                const recursoId = document.querySelector('select[name="recurso_id"]').value;
                const numeroPersonas = document.querySelector('input[name="numero_personas"]').value;
                
                if (recursoId && numeroPersonas) {
                    const recursos = <?php echo json_encode($recursos); ?>;
                    const recursoSeleccionado = recursos.find(r => r.id == recursoId);
                    if (recursoSeleccionado) {
                        const precioTotal = recursoSeleccionado.precio * numeroPersonas;
                        document.querySelector('output[name="precio_total"]').textContent = precioTotal.toFixed(2) + '€';
                    }
                }
            }
            
            document.querySelector('select[name="recurso_id"]').addEventListener('change', actualizarPrecio);
            document.querySelector('input[name="numero_personas"]').addEventListener('input', actualizarPrecio);
        });
    </script>
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
    
    <p>Estás en: <a href="../index.html">Inicio</a> >> <a href="../reservas.php">Reservas</a> >> Realizar Reserva</p>
    
    <main>
        <h1>Realizar Reserva</h1>
        
        <?php if (!empty($mensaje)): ?>
            <section>
                <p><?php echo htmlspecialchars($mensaje); ?></p>
                <p><a href="mis_reservas.php" class="button">Ver mis reservas</a></p>
                <p><a href="../reservas.php" class="button">Volver a Reservas</a></p>
            </section>
        <?php elseif (!empty($error)): ?>
            <section>
                <p><?php echo htmlspecialchars($error); ?></p>
            </section>
        <?php endif; ?>
        
        <section>
            <h2>Formulario de Reserva</h2>
            <section>
                <h3>Información de Reservas</h3>
                <ul>
                    <li>El precio se calcula por persona.</li>
                    <li>Cada recurso tiene un límite de ocupación máximo.</li>
                    <li>Todas las reservas son para un día completo.</li>
                </ul>
            </section>

            <?php if (empty($mensaje)): ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <article>
                    <legend>Datos de la reserva</legend>
                    
                    <label for="recurso_id">Recurso turístico:</label>
                    <select name="recurso_id" required>
                        <option value="">Seleccione un recurso</option>
                        <?php foreach ($recursos as $recurso): ?>
                            <option value="<?php echo $recurso['id']; ?>" <?php echo (isset($_POST['recurso_id']) && $_POST['recurso_id'] == $recurso['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($recurso['nombre']); ?>
                                (<?php echo $recurso['precio']; ?>€/persona)
                                - Disponible: 
                                <?php 
                                    $fecha_hora_inicio = (isset($recurso['fecha_hora_inicio']) && $recurso['fecha_hora_inicio'] !== null && $recurso['fecha_hora_inicio'] !== '') 
                                        ? date('d/m/Y H:i', strtotime($recurso['fecha_hora_inicio'])) 
                                        : 'No disponible'; 
                                    
                                    $fecha_hora_fin = (isset($recurso['fecha_hora_fin']) && $recurso['fecha_hora_fin'] !== null && $recurso['fecha_hora_fin'] !== '') 
                                        ? date('d/m/Y H:i', strtotime($recurso['fecha_hora_fin'])) 
                                        : 'No disponible';
                                    
                                    echo $fecha_hora_inicio . ' a ' . $fecha_hora_fin;
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="numero_personas">Número de personas:</label>
                    <input type="number" name="numero_personas" required min="1" value="<?php echo isset($_POST['numero_personas']) ? intval($_POST['numero_personas']) : 1; ?>">
                    
                    <p>Precio total estimado: <output name="precio_total">0.00€</output></p>
                    
                    <button type="submit" name="generar_presupuesto" class="button">Generar Presupuesto</button>
                </article>
            </form>
            <?php endif; ?>
        </section>
        
        <?php if (!empty($presupuesto) && empty($mensaje)): ?>
        <section>
            <h2>Presupuesto</h2>
            <article>
                <h2>Datos de la reserva</h2>
                <p><strong>Recurso:</strong> <?php echo htmlspecialchars($presupuesto['recurso_nombre']); ?></p>
                <p><strong>Número de personas:</strong> <?php echo $presupuesto['numero_personas']; ?></p>
                <p><strong>Precio por persona:</strong> <?php echo $presupuesto['precio_unitario']; ?>€</p>
                <p><strong>Precio total:</strong> <?php echo $presupuesto['precio_total']; ?>€</p>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <input type="hidden" name="recurso_id" value="<?php echo $presupuesto['recurso_id']; ?>">
                    <input type="hidden" name="numero_personas" value="<?php echo $presupuesto['numero_personas']; ?>">
                    <input type="hidden" name="precio_total" value="<?php echo $presupuesto['precio_total']; ?>">
                    <button type="submit" name="confirmar_reserva" class="button">Confirmar Reserva</button>
                </form>
            </article>
        </section>
        <?php endif; ?>
    </main>

    <footer>
        <p>2025 Turismo de Muros del Nalón</p>
        <p><a href="https://www.uniovi.es">Universidad de Oviedo</a> 
            - <a href="https://www.uniovi.es/estudia/grados/ingenieria/informaticasoftware/-/fof/asignatura/GIISOF01-3-002">Software y Estándares para la Web</a></p>
        <p><a href="https://github.com/alejandrofdzgarcia">Diseñado por Alejandro Fernández García</a></p>
    </footer>
</body>
</html>
