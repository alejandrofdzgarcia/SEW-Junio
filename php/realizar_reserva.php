<?php
session_start();

require_once 'DBManager.php';

class ReservaManager {
    private $db;
    private $usuario_id;
    private $mensaje = '';
    private $error = '';
    private $recursos = [];
    private $presupuesto = null;

    public function __construct($usuario_id) {
        if (!isset($usuario_id)) {
            header('Location: login.php');
            exit;
        }
        
        $this->usuario_id = $usuario_id;
        
        $dbManager = new DBManager();
        $this->db = $dbManager->getConnection();
        
        $this->cargarRecursos();
    }
    
    public function __destruct() {
        if ($this->db && $this->db instanceof mysqli) {
            $dbManager = new DBManager();
            $dbManager->closeConnection();
        }
    }
    
    private function cargarRecursos() {
        try {
            $query = "SELECT id, nombre, descripcion, precio, limite_ocupacion FROM recursos_turisticos";
            $resultado = $this->db->query($query);
            
            if ($resultado && $resultado->num_rows > 0) {
                while ($fila = $resultado->fetch_assoc()) {
                    $this->recursos[] = $fila;
                }
            }
        } catch (Exception $e) {
            $this->error = "Error al obtener recursos: " . $e->getMessage();
        }
    }
    
    public function generarPresupuesto($recurso_id, $numero_personas) {
        if ($numero_personas <= 0) {
            $this->error = "El número de personas debe ser mayor que cero.";
            return false;
        }
        
        try {
            $query = "SELECT nombre, precio, limite_ocupacion FROM recursos_turisticos WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $recurso_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $recurso = $result->fetch_assoc();
            
            if ($numero_personas > $recurso['limite_ocupacion']) {
                $this->error = "El número de personas excede el límite de ocupación del recurso (" . $recurso['limite_ocupacion'] . " personas).";
                return false;
            }
            
            $precio_total = $recurso['precio'] * $numero_personas;
            $this->presupuesto = [
                'recurso_id' => $recurso_id,
                'recurso_nombre' => $recurso['nombre'],
                'numero_personas' => $numero_personas,
                'precio_unitario' => $recurso['precio'],
                'precio_total' => $precio_total
            ];
            
            return true;
        } catch (Exception $e) {
            $this->error = "Error al generar presupuesto: " . $e->getMessage();
            return false;
        }
    }
    
    public function confirmarReserva($recurso_id, $numero_personas, $precio_total) {
        try {
            $query = "SELECT limite_ocupacion FROM recursos_turisticos WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $recurso_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $recurso = $result->fetch_assoc();
            
            if ($numero_personas > $recurso['limite_ocupacion']) {
                $this->error = "El número de personas excede el límite de ocupación del recurso.";
                return false;
            }
            
            $query = "INSERT INTO reservas (usuario_id, recurso_id, numero_personas, precio_total, estado) 
                     VALUES (?, ?, ?, ?, 'confirmada')";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iiid', $this->usuario_id, $recurso_id, $numero_personas, $precio_total);
            
            if ($stmt->execute()) {
                $this->mensaje = "¡Reserva realizada con éxito! Precio total: " . $precio_total . "€";
                return true;
            } else {
                $this->error = "Error al realizar la reserva: " . $this->db->error;
                return false;
            }
        } catch (Exception $e) {
            $this->error = "Error en la reserva: " . $e->getMessage();
            return false;
        }
    }
    
    public function getRecursos() {
        return $this->recursos;
    }
    
    public function getPresupuesto() {
        return $this->presupuesto;
    }
    
    public function getMensaje() {
        return $this->mensaje;
    }
    
    public function getError() {
        return $this->error;
    }
}

$reservaManager = new ReservaManager($_SESSION['usuario_id'] ?? null);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generar_presupuesto'])) {
        $recurso_id = (int)$_POST['recurso_id'];
        $numero_personas = (int)$_POST['numero_personas'];
        $reservaManager->generarPresupuesto($recurso_id, $numero_personas);
    }
    
    if (isset($_POST['confirmar_reserva'])) {
        $recurso_id = (int)$_POST['recurso_id'];
        $numero_personas = (int)$_POST['numero_personas'];
        $precio_total = (float)$_POST['precio_total'];
        $reservaManager->confirmarReserva($recurso_id, $numero_personas, $precio_total);
    }
}

$recursos = $reservaManager->getRecursos();
$presupuesto = $reservaManager->getPresupuesto();
$mensaje = $reservaManager->getMensaje();
$error = $reservaManager->getError();
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
                <p><a href="../reservas.php">Volver a Reservas</a></p>
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
                    <li>El precio se calcula por día completo, incluyendo el día de inicio y fin.</li>
                </ul>
            </section>

            <?php if (empty($mensaje)): ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <fieldset>
                    <legend>Datos de la reserva</legend>
                    
                    <label>Recurso turístico:</label>
                    <select name="recurso_id" required>
                        <option value="">Seleccione un recurso</option>
                        <?php foreach ($recursos as $recurso): ?>
                            <option value="<?php echo $recurso['id']; ?>">
                                <?php echo htmlspecialchars($recurso['nombre']); ?>
                                (<?php echo $recurso['precio']; ?>€/día)
                            </option>
                        <?php endforeach; ?>
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
