<?php
/**
 * Script para visualizar las reservas del usuario actual
 * 
 * @author Alejandro Fernández García - UO295813
 * @version 1.0
 */

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    // Si no está logueado, redirigir a la página de login
    header('Location: login.php');
    exit;
}

// Incluir la clase DBManager
require_once 'DBManager.php';

// Inicializar el gestor de base de datos
$dbManager = new DBManager();
$db = $dbManager->getConnection();

$usuario_id = $_SESSION['usuario_id'];
$reservas = [];
$error = '';

// Obtener las reservas del usuario
try {
    $query = "SELECT r.id, r.fecha_reserva, r.numero_personas, r.precio_total, r.estado,
                     rt.nombre as recurso_nombre, rt.descripcion as recurso_descripcion,
                     rt.fecha_hora_inicio, rt.fecha_hora_fin
              FROM reservas r
              JOIN recursos_turisticos rt ON r.recurso_id = rt.id
              WHERE r.usuario_id = ?
              ORDER BY r.fecha_reserva DESC";
              
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado && $resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $reservas[] = $fila;
        }
    }
} catch (Exception $e) {
    $error = "Error al obtener reservas: " . $e->getMessage();
}

// Cerrar la conexión
$dbManager->closeConnection();
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
    <link rel="stylesheet" type="text/css" href="../estilo/reservas.css">
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
    
    <p>Estás en: <a href="../index.html">Inicio</a> >> <a href="../reservas.php">Reservas</a> >> Mis Reservas</p>
    
    <main>
        <h1>Mis Reservas</h1>
          <?php if (!empty($error)): ?>
            <section>
                <p><?php echo htmlspecialchars($error); ?></p>
            </section>
        <?php endif; ?>
        
        <section>
            <h2>Listado de Reservas</h2>
            
            <?php if (empty($reservas)): ?>
                <div class="info-box">
                    <p>No tienes reservas activas en este momento.</p>
                    <p><a href="realizar_reserva.php" class="button">Realizar una reserva</a></p>
                </div>
            <?php else: ?>
                <div class="reservas-grid">
                    <?php foreach ($reservas as $reserva): ?>
                        <div class="reserva-card">                            <h3><?php echo htmlspecialchars($reserva['recurso_nombre']); ?></h3>
                            <p class="reserva-fechas">
                                <strong>Fechas:</strong> 
                                <?php echo date('d/m/Y H:i', strtotime($reserva['fecha_hora_inicio'])); ?> - 
                                <?php echo date('d/m/Y H:i', strtotime($reserva['fecha_hora_fin'])); ?>
                            </p>
                            <p class="reserva-personas">
                                <strong>Personas:</strong> 
                                <?php echo $reserva['numero_personas']; ?>
                            </p>
                            <p class="reserva-precio">
                                <strong>Precio Total:</strong> 
                                <?php echo number_format($reserva['precio_total'], 2); ?>€
                            </p>
                            <p class="reserva-estado">
                                <strong>Estado:</strong> 
                                <?php echo htmlspecialchars($reserva['estado']); ?>
                            </p>
                            <p class="reserva-descripcion">
                                <?php echo htmlspecialchars($reserva['recurso_descripcion']); ?>
                            </p>
                            <div class="reserva-footer">
                                <span class="reserva-id">ID: <?php echo $reserva['id']; ?></span>
                                <span class="reserva-fecha">
                                    Reservado el: <?php echo date('d/m/Y', strtotime($reserva['fecha_reserva'])); ?>
                                </span>
                            </div>
                            <div class="reserva-actions">
                                <a href="cancelar_reserva.php?id=<?php echo $reserva['id']; ?>" class="button">Cancelar</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <p class="actions-footer">
                    <a href="realizar_reserva.php" class="button">Realizar otra reserva</a>
                    <a href="../reservas.php" class="button secondary">Volver a Reservas</a>
                </p>
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
