<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../multimedia/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Importar/Exportar CSV - Muros del Nalón</title>
    <meta name="author" content="Alejandro Fernández García"/>
    <meta name="description" content="Importar y exportar datos CSV - Muros del Nalón"/>
    <meta name="keywords" content="csv, importar, exportar, datos, base de datos"/> 
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css">
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css">
</head>
<body>
    <?php
    require_once 'DBManager.php';
    $dbManager = new DBManager();
    $dbManager->createDatabase();
    $tables = $dbManager->getAllTables();
    ?>
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

    <main>
        <h2>Gestión de Datos CSV</h2>
        <p>En esta sección puedes importar datos desde archivos CSV a la base de datos o exportar datos de la base de datos a archivos CSV.</p>
        
        <section>
            <h3>Importar datos desde CSV</h3>
            <form method="post" enctype="multipart/form-data" action="importar_exportar_csv.php">
                <label for="csv_file">Seleccionar archivo CSV:</label>
                <input type="file" name="csv_file" accept=".csv" required>
                <p><small>El archivo debe estar en formato CSV con separador de comas (,). El sistema detectará automáticamente la tabla a partir del contenido del archivo.</small></p>
                
                <button type="submit" name="import">Importar datos</button>
            </form>
            
            <?php
            if (isset($_POST['import']) && isset($_FILES['csv_file'])) {
                $tmpFile = $_FILES['csv_file']['tmp_name'];
                
                if (!empty($tmpFile) && file_exists($tmpFile)) {
                    try {
                        $importResults = $dbManager->importFromCSV($tmpFile);
                        
                        if (!empty($importResults)) {
                            echo '<div class="success-message">';
                            echo '<p>Datos importados correctamente:</p>';
                            echo '<ul>';
                            foreach ($importResults as $table => $count) {
                                echo '<li>Tabla <strong>' . htmlspecialchars($table) . '</strong>: ' . $count . ' registros importados</li>';
                            }
                            echo '</ul>';
                            echo '</div>';
                        } else {
                            echo '<p>No se encontraron datos para importar en el archivo CSV.</p>';
                        }
                    } catch (Exception $e) {
                        echo '<p class="error-message">Error al importar datos: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                } else {
                    echo '<p class="error-message">No se ha podido procesar el archivo</p>';
                }
            }
            ?>
        </section>
        
        <section>
            <h3>Exportar datos a CSV</h3>
            <form method="post" action="importar_exportar_csv.php">
                <label for="export_table">Seleccionar tabla origen:</label>
                <select name="export_table" required>
                    <option value="">-- Seleccionar tabla --</option>
                    <option value="todas">Todas las tablas</option>
                    <?php foreach ($tables as $table): ?>
                    <option value="<?php echo htmlspecialchars($table); ?>"><?php echo htmlspecialchars($table); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" name="export">Exportar datos</button>
            </form>
            
            <?php
            if (isset($_POST['export']) && isset($_POST['export_table'])) {
                $tabla = $_POST['export_table'];
                
                try {
                    // La función exportToCSV ahora maneja directamente la descarga
                    $dbManager->exportToCSV($tabla);
                    // Nota: el código de descarga no se ejecutará porque exportToCSV ya realiza un exit()
                } catch (Exception $e) {
                    echo '<p class="error-message">Error al exportar datos: ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
            }
            ?>
        </section>
    </main>

    <footer>
        <p>2025 Turismo de Muros del Nalón</p>
        <p><a href="https://www.uniovi.es">Universidad de Oviedo</a> 
            - <a href="https://www.uniovi.es/estudia/grados/ingenieria/informaticasoftware/-/fof/asignatura/GIISOF01-3-002">Software y Estándares para la Web</a></p>
        <p><a href="https://github.com/alejandrofdzgarcia">Diseñado por Alejandro Fernández García</a></p>
    </footer>
    <?php $dbManager->closeConnection(); ?>
</body>
</html>