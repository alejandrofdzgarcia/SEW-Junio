<?php
session_start();

require_once 'DBManager.php';
$dbManager = new DBManager();
$dbManager->createDatabase();

$usuarioLogueado = isset($_SESSION['usuario_id']);
$nombreUsuario = $usuarioLogueado ? $_SESSION['usuario_nombre'] : '';

try {
    $db = $dbManager->getConnection();
    
    $query = "SELECT * FROM recursos_turisticos ORDER BY nombre";
    $result = $db->query($query);
    
    if (!$result) {
        throw new Exception("Error al consultar recursos turísticos: " . $db->error);
    }
    
    $recursos = [];
    while ($row = $result->fetch_assoc()) {
        $recursos[] = $row;
    }
    
} catch (Exception $e) {
    $errorDB = "Error: " . $e->getMessage();
}

$dbManager->closeConnection();
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
    <link rel="stylesheet" type="text/css" href="../estilo/reservas.css">
</head>
<body>
    <header>
        <h1><a href="../index.html" title="Inicio Muros del Nalón">Muros del Nalón</a></h1>
        <nav>
            <a href="../index.html" title="Inicio Muros del Nalón">Página principal</a>
            <a href="../gastronomia.html" title="Gastronomía Muros del Nalón">Gastronomía</a>
            <a href="../rutas.html" title="Rutas Muros del Nalón">Rutas</a>            <a href="../meteorologia.html" title="Meteorología Muros del Nalón">Meteorología</a>
            <a href="../juego.html" title="Juego Muros del Nalón">Juego</a>
            <a href="../reservas.php" title="Reservas Muros del Nalón" style="font-weight: bold; text-decoration: underline;">Reservas</a>
            <a href="../ayuda.html" title="Ayuda Muros del Nalón">Ayuda</a>
        </nav>
    </header>
    
    <p>Estás en: <a href="../index.html">Inicio</a> >> <a href="../reservas.php">Reservas</a> >> Catálogo de Recursos</p>
    
    <main>
        <h1>Catálogo de Recursos Turísticos</h1>
          <?php if(isset($errorDB)): ?>
            <section>
                <p><?php echo $errorDB; ?></p>
                <p>Por favor, inténtelo de nuevo más tarde o contacte con el administrador.</p>
            </section>
        <?php else: ?>
        
        <section>
            <p>Descubre todos los recursos turísticos disponibles en Muros del Nalón.</p>
            
            <?php if($usuarioLogueado): ?>
                <p>Hola, <strong><?php echo htmlspecialchars($nombreUsuario); ?></strong>. 
                Para hacer una reserva, haz clic en el botón "Reservar" junto al recurso que te interese.</p>
            <?php else: ?>
                <p>Para poder realizar reservas, debes <a href="login.php">iniciar sesión</a> o <a href="register.php">registrarte</a>.</p>
            <?php endif; ?>
        </section>
        
        <section>
            <?php if(empty($recursos)): ?>
                <p>No hay recursos turísticos disponibles en este momento.</p>
            <?php else: ?>
                <?php foreach($recursos as $recurso): ?>
                    <fieldset>
                        <legend><?php echo htmlspecialchars($recurso['nombre']); ?></legend>
                        
                        <p><strong>Descripción:</strong> <?php echo htmlspecialchars($recurso['descripcion']); ?></p>
                        
                        <p><strong>Límite de ocupación:</strong> <?php echo htmlspecialchars($recurso['limite_ocupacion']); ?> personas</p>
                        
                        <p><strong>Fecha y hora de inicio:</strong> 
                            <?php echo date('d/m/Y H:i', strtotime($recurso['fecha_hora_inicio'])); ?>
                        </p>
                        
                        <p><strong>Fecha y hora de fin:</strong> 
                            <?php echo date('d/m/Y H:i', strtotime($recurso['fecha_hora_fin'])); ?>
                        </p>
                        
                        <p><strong>Precio:</strong> 
                            <?php echo number_format($recurso['precio'], 2, ',', '.'); ?> €
                        </p>
                        
                        <?php if($usuarioLogueado): ?>
                            <p>
                                <a href="realizar_reserva.php?id=<?php echo $recurso['id']; ?>">Reservar</a>
                            </p>
                        <?php endif; ?>
                    </fieldset>
                    <hr>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
          <?php endif; ?>
        
        <section>
            <p><a href="../reservas.php">Volver a la Central de Reservas</a></p>
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
