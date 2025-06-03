<?php
<<<<<<< HEAD
session_start();

if (!isset($_SESSION['usuario_id'])) {
=======
/**
 * Script para realizar reservas de recursos turísticos
 * 
 * @author Alejandro Fernández García - UO295813
 * @version 1.0
 */

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    // Si no está logueado, redirigir a la página de login
>>>>>>> 83776fc979782181856940c1059d88f87d4916a7
    header('Location: login.php');
    exit;
}

<<<<<<< HEAD
require_once 'DBManager.php';
=======
// Incluir la clase DBManager
require_once 'DBManager.php';

// Inicializar el gestor de base de datos
>>>>>>> 83776fc979782181856940c1059d88f87d4916a7
$dbManager = new DBManager();
$db = $dbManager->getConnection();

$usuario_id = $_SESSION['usuario_id'];
$mensaje = '';
$error = '';
$recursos = [];
$presupuesto = null;

<<<<<<< HEAD
=======
// Obtener todos los recursos turísticos disponibles
>>>>>>> 83776fc979782181856940c1059d88f87d4916a7
try {
    $query = "SELECT id, nombre, descripcion, precio, limite_ocupacion FROM recursos_turisticos";
    $resultado = $db->query($query);
    
    if ($resultado && $resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $recursos[] = $fila;
        }
    }
} catch (Exception $e) {
    $error = "Error al obtener recursos: " . $e->getMessage();
}

<<<<<<< HEAD
=======
// Procesar la generación de presupuesto
>>>>>>> 83776fc979782181856940c1059d88f87d4916a7
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generar_presupuesto'])) {
    $recurso_id = (int)$_POST['recurso_id'];
    $numero_personas = (int)$_POST['numero_personas'];
    
<<<<<<< HEAD
=======
    // Validar número de personas
>>>>>>> 83776fc979782181856940c1059d88f87d4916a7
    if ($numero_personas <= 0) {
        $error = "El número de personas debe ser mayor que cero.";
    } else {
        try {
<<<<<<< HEAD
=======
            // Obtener información del recurso
>>>>>>> 83776fc979782181856940c1059d88f87d4916a7
            $query = "SELECT nombre, precio, limite_ocupacion FROM recursos_turisticos WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param('i', $recurso_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $recurso = $result->fetch_assoc();
            
<<<<<<< HEAD
            if ($numero_personas > $recurso['limite_ocupacion']) {
                $error = "El número de personas excede el límite de ocupación del recurso (" . $recurso['limite_ocupacion'] . " personas).";
            } else {
                $precio_total = $recurso['precio'] * $numero_personas;
                
=======
            // Verificar límite de ocupación
            if ($numero_personas > $recurso['limite_ocupacion']) {
                $error = "El número de personas excede el límite de ocupación del recurso (" . $recurso['limite_ocupacion'] . " personas).";
            } else {
                // Calcular el precio total
                $precio_total = $recurso['precio'] * $numero_personas;
                
                // Generar presupuesto
>>>>>>> 83776fc979782181856940c1059d88f87d4916a7
                $presupuesto = [
                    'recurso_id' => $recurso_id,
                    'recurso_nombre' => $recurso['nombre'],
                    'numero_personas' => $numero_personas,
                    'precio_unitario' => $recurso['precio'],
                    'precio_total' => $precio_total
                ];
            }
        } catch (Exception $e) {
            $error = "Error al generar presupuesto: " . $e->getMessage();
        }
    }
}

<<<<<<< HEAD
=======
// Procesar el formulario de reserva
>>>>>>> 83776fc979782181856940c1059d88f87d4916a7
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_reserva'])) {
    $recurso_id = (int)$_POST['recurso_id'];
    $numero_personas = (int)$_POST['numero_personas'];
    $precio_total = (float)$_POST['precio_total'];
    
    try {
<<<<<<< HEAD
=======
        // Verificar disponibilidad del recurso
>>>>>>> 83776fc979782181856940c1059d88f87d4916a7
        $query = "SELECT limite_ocupacion FROM recursos_turisticos WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param('i', $recurso_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $recurso = $result->fetch_assoc();
        
        if ($numero_personas > $recurso['limite_ocupacion']) {
            $error = "El número de personas excede el límite de ocupación del recurso.";
        } else {
<<<<<<< HEAD
=======
            // Realizar la reserva
>>>>>>> 83776fc979782181856940c1059d88f87d4916a7
            $query = "INSERT INTO reservas (usuario_id, recurso_id, numero_personas, precio_total, estado) 
                     VALUES (?, ?, ?, ?, 'confirmada')";
            $stmt = $db->prepare($query);
            $stmt->bind_param('iiid', $usuario_id, $recurso_id, $numero_personas, $precio_total);
            
            if ($stmt->execute()) {
                $mensaje = "¡Reserva realizada con éxito! Precio total: " . $precio_total . "€";
            } else {
                $error = "Error al realizar la reserva: " . $db->error;
            }
        }
    } catch (Exception $e) {
        $error = "Error en la reserva: " . $e->getMessage();
    }
}

<<<<<<< HEAD
=======
// Cerrar la conexión
>>>>>>> 83776fc979782181856940c1059d88f87d4916a7
$dbManager->closeConnection();
?>

<!DOCTYPE html>
<html lang="es">
<head>    
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="multimedia/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Reservar - Muros del Nalón</title>
    <meta name="author" content="Alejandro Fernández García"/>
    <meta name="description" content="Reservar - Muros del Nalón"/>    <meta name="keywords" content="reservar, central"/>      
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css">
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css">
    <link rel="stylesheet" type="text/css" href="../estilo/reservas.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
<<<<<<< HEAD
=======
            // Actualizar el precio total cuando cambie la selección de recurso o fechas
>>>>>>> 83776fc979782181856940c1059d88f87d4916a7
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
        <h1>Realizar Reserva</h1>          <?php if (!empty($mensaje)): ?>
            <section>
                <p><?php echo htmlspecialchars($mensaje); ?></p>
                <p><a href="../reservas.php">Volver a Reservas</a></p>
            </section>
        <?php elseif (!empty($error)): ?>
            <section>
                <p><?php echo htmlspecialchars($error); ?></p>
            </section>
        <?php endif; ?>
        
        <section>
            <h2>Formulario de Reserva</h2>            <section>
                <h3>Información de Reservas</h3>
                <ul>
<<<<<<< HEAD
=======
                    <li>Las reservas deben realizarse con al menos 24 horas de anticipación.</li>
>>>>>>> 83776fc979782181856940c1059d88f87d4916a7
                    <li>El precio se calcula por día completo, incluyendo el día de inicio y fin.</li>
                </ul>
            </section>

            <?php if (empty($mensaje)): ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <fieldset>                    <legend>Datos de la reserva</legend>
                    
                    <label>Recurso turístico:</label>
                    <select name="recurso_id" required>
                        <option value="">Seleccione un recurso</option>
                        <?php foreach ($recursos as $recurso): ?>
                            <option value="<?php echo $recurso['id']; ?>">
                                <?php echo htmlspecialchars($recurso['nombre']); ?>
                                (<?php echo $recurso['precio']; ?>€/día)
                            </option>                        <?php endforeach; ?>
                    </select>
                    
                    <label>Número de personas:</label>
                    <input type="number" name="numero_personas" required min="1" value="1">
                    
                    <p>Precio total: <output name="precio_total">0.00€</output></p>
                    
                    <button type="submit" name="generar_presupuesto">Generar Presupuesto</button>
                </fieldset>
            </form>
            <?php endif; ?>
        </section>
        
        <?php if (!empty($presupuesto)): ?>
        <section>
            <h2>Presupuesto</h2>
            <p>Recurso: <?php echo htmlspecialchars($presupuesto['recurso_nombre']); ?></p>
            <p>Número de personas: <?php echo $presupuesto['numero_personas']; ?></p>
            <p>Precio unitario: <?php echo $presupuesto['precio_unitario']; ?>€/persona</p>
            <p>Precio total: <?php echo $presupuesto['precio_total']; ?>€</p>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <input type="hidden" name="recurso_id" value="<?php echo $presupuesto['recurso_id']; ?>">
                <input type="hidden" name="numero_personas" value="<?php echo $presupuesto['numero_personas']; ?>">
                <input type="hidden" name="precio_total" value="<?php echo $presupuesto['precio_total']; ?>">
                <button type="submit" name="confirmar_reserva">Confirmar Reserva</button>
            </form>
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
