<?php
/**
 * Clase DBManager - Gestiona la conexión a la base de datos para el sitio de Muros del Nalón
 * 
 * @author Alejandro Fernández García - UO295813
 * @version 1.1
 */
class DBManager {
    private $server = "localhost";
    private $user = "DBUSER2025";
    private $pass = "DBPWD2025";
    private $dbname = "muros_nalon";
    private $db;    
    
    /**
     * Constructor de la clase DBManager
     * Inicializa la conexión a la base de datos
     */    
    public function __construct() {
        $this->connect();
    }

    /**
     * Establece la conexión a la base de datos
     */
    private function connect() {
        $this->db = new mysqli($this->server, $this->user, $this->pass);
        
        if ($this->db->connect_error) {
            die("Error de conexión a la base de datos: " . $this->db->connect_error);
        }
        
        $this->db->set_charset("utf8");
    }

    /**
     * Obtiene la conexión a la base de datos
     * 
     * @return mysqli Conexión a la base de datos
     */
    public function getConnection() {
        if (!$this->db || $this->db->connect_errno) {
            $this->connect();
        }
        
        $this->db->select_db($this->dbname);
        return $this->db;
    }

    /**
     * Crea la base de datos si no existe
     * Ejecuta el archivo SQL de inicialización solo si la base de datos se crea por primera vez
     */
    public function createDatabase() {
        $result = $this->db->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$this->dbname}'");
        $dbExists = ($result && $result->num_rows > 0);
        
        if (!$dbExists) {
            if ($this->db->query("CREATE DATABASE IF NOT EXISTS {$this->dbname}") === TRUE) {
                $this->db->select_db($this->dbname);
                $this->executeSQLFile(__DIR__ . "/muros_nalon.sql");
            } else {
                die("Error al crear la base de datos: " . $this->db->error);
            }
        } else {
            $this->db->select_db($this->dbname);
        }
    }

    /**
     * Ejecuta un archivo SQL
     * 
     * @param string $filePath Ruta al archivo SQL
     */
    private function executeSQLFile($filePath) {
        if (!file_exists($filePath)) {
            die("Archivo SQL no encontrado: " . $filePath);
        }

        $sqlContent = file_get_contents($filePath);
        $queries = array_filter(array_map('trim', explode(';', $sqlContent)));

        foreach ($queries as $query) {
            if (!empty($query) && $this->db->query($query) === FALSE) {
                echo "Error al ejecutar consulta: " . $this->db->error . "<br>";
            }
        }
    }
    
    /**
     * Importa datos desde un archivo CSV
     * 
     * @param string $csvFilePath Ruta al archivo CSV
     * @return array Información sobre las tablas importadas y número de registros
     */
    public function importFromCSV($csvFilePath) {
        if (!file_exists($csvFilePath)) {
            die("Archivo CSV no encontrado: " . $csvFilePath);
        }
        
        // Eliminar BOM UTF-8 si existe
        $content = file_get_contents($csvFilePath);
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $content = substr($content, 3);
            file_put_contents($csvFilePath, $content);
        }
        
        $file = fopen($csvFilePath, "r");
        $importResults = [];
        $currentTable = null;
        $headers = null;
        $rowsImported = 0;
        
        while (($line = fgetcsv($file)) !== FALSE) {
            if (empty($line) || (count($line) === 1 && empty($line[0]))) {
                continue;
            }
            
            if (count($line) === 1 && strpos($line[0], 'TABLA:') === 0) {
                if ($currentTable !== null && $rowsImported > 0) {
                    $importResults[$currentTable] = $rowsImported;
                }
                
                $currentTable = trim(substr($line[0], 6));
                $headers = null;
                $rowsImported = 0;
                continue;
            }
            
            if ($currentTable !== null && $headers === null) {
                $headers = $line;
                
                $tableCheck = $this->db->query("SHOW TABLES LIKE '{$currentTable}'");
                if ($tableCheck->num_rows == 0) {
                    echo "La tabla {$currentTable} no existe en la base de datos.<br>";
                    $currentTable = null;
                }
                continue;
            }
            
            if ($currentTable !== null && $headers !== null) {
                $line = count($line) < count($headers) ? array_pad($line, count($headers), null) : 
                       (count($line) > count($headers) ? array_slice($line, 0, count($headers)) : $line);
                
                if ($this->insertRowIfNotExists($currentTable, $headers, $line)) {
                    $rowsImported++;
                }
            }
        }
        
        if ($currentTable !== null && $rowsImported > 0) {
            $importResults[$currentTable] = $rowsImported;
        }
        
        fclose($file);
        return $importResults;
    }

    /**
     * Inserta una fila en la tabla si no existe ya
     * 
     * @param string $tableName Nombre de la tabla
     * @param array $headers Nombres de columnas
     * @param array $data Datos a insertar
     * @return bool Éxito de la operación
     */
    private function insertRowIfNotExists($tableName, $headers, $data) {
        
        $tableColumns = [];
        $result = $this->db->query("DESCRIBE {$tableName}");
        while ($column = $result->fetch_assoc()) {
            $tableColumns[] = $column['Field'];
        }
        
        $validColumns = [];
        $validData = [];
        $emailColumnIndex = -1;
        $emailValue = null;
        
        foreach ($headers as $index => $column) {
            if (in_array($column, $tableColumns)) {
                $validColumns[] = $column;
                $validData[] = $data[$index];
                
                if (preg_match('/(email|correo)/i', $column)) {
                    $emailColumnIndex = count($validColumns) - 1;
                    $emailValue = $data[$index];
                }
            }
        }
        
        if (empty($validColumns)) {
            return false;
        }
        
        // Verificar email duplicado
        if ($emailColumnIndex >= 0 && !empty($emailValue)) {
            $emailColumn = $validColumns[$emailColumnIndex];
            $stmt = $this->db->prepare("SELECT COUNT(*) AS count FROM {$tableName} WHERE {$emailColumn} = ?");
            
            if ($stmt) {
                $stmt->bind_param('s', $emailValue);
                $stmt->execute();
                $result = $stmt->get_result();
                $emailExists = $result->fetch_assoc()['count'] > 0;
                $stmt->close();
                
                if ($emailExists) {
                    return false;
                }
            }
        }
        
        // Comprobar si el registro ya existe
        $where = [];
        $params = [];
        $types = '';
        
        foreach ($validColumns as $index => $column) {
            $value = $validData[$index];
            if ($value === null || $value === '') {
                $where[] = "({$column} IS NULL OR {$column} = '')";
            } else {
                $where[] = "{$column} = ?";
                $params[] = $value;
                $types .= 's';
            }
        }
        
        if (empty($where)) {
            return false;
        }
        
        $sql = "SELECT COUNT(*) AS count FROM {$tableName} WHERE " . implode(" AND ", $where);
        $stmt = $this->db->prepare($sql);
        
        if ($stmt && !empty($params)) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->fetch_assoc()['count'] > 0;
            $stmt->close();
            
            // Si no existe, insertar
            if (!$exists) {
                $columns = implode(",", $validColumns);
                $placeholders = implode(",", array_fill(0, count($validColumns), '?'));
                $stmt = $this->db->prepare("INSERT INTO {$tableName} ({$columns}) VALUES ({$placeholders})");
                
                if ($stmt) {
                    $stmt->bind_param(str_repeat('s', count($validData)), ...$validData);
                    $result = $stmt->execute();
                    $stmt->close();
                    return $result;
                }
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Exporta datos de una tabla específica o todas las tablas para su descarga
     * 
     * @param string $tableName Nombre de la tabla o "todas" para exportar todas las tablas
     */
    public function exportToCSV($tableName) {
        if (ob_get_level()) ob_end_clean();
        
        if ($tableName === 'todas') {
            $this->exportAllTables();
        } else {
            $this->exportSingleTable($tableName);
        }
    }
    
    /**
     * Exporta todas las tablas a un único archivo CSV
     */
    private function exportAllTables() {
        $tables = $this->getAllTables();
        
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="muros_nalon_todas_tablas.csv"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo "\xEF\xBB\xBF"; // BOM UTF-8
        
        $output = fopen('php://output', 'w');
        
        foreach ($tables as $table) {
            fputcsv($output, ["TABLA: " . $table]);
            
            $result = $this->db->query("SELECT * FROM {$table}");
            
            if (!$result) {
                fputcsv($output, ["Error al consultar la tabla {$table}: " . $this->db->error]);
                continue;
            }
            
            // Filtrar campos ID
            $headers = [];
            $fieldsInfo = $result->fetch_fields();
            foreach ($fieldsInfo as $field) {
                if (strtolower($field->name) != 'id' && !preg_match('/_id$/i', $field->name)) {
                    $headers[] = $field->name;
                }
            }
            
            fputcsv($output, $headers);
            
            while ($row = $result->fetch_assoc()) {
                $filteredRow = [];
                foreach ($row as $key => $value) {
                    if (strtolower($key) != 'id' && !preg_match('/_id$/i', $key)) {
                        $filteredRow[] = $value;
                    }
                }
                fputcsv($output, $filteredRow);
            }
            
            fputcsv($output, [""]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Exporta una sola tabla a CSV
     * 
     * @param string $tableName Nombre de la tabla
     */
    private function exportSingleTable($tableName) {
        $output = fopen('php://temp', 'r+');
        
        fputcsv($output, ["TABLA: " . $tableName]);
        
        $result = $this->db->query("SELECT * FROM {$tableName}");
        
        if (!$result) {
            die("Error al consultar la tabla {$tableName}: " . $this->db->error);
        }
        
        // Filtrar campos ID
        $headers = [];
        $fieldsInfo = $result->fetch_fields();
        foreach ($fieldsInfo as $field) {
            if (strtolower($field->name) != 'id' && !preg_match('/_id$/i', $field->name)) {
                $headers[] = $field->name;
            }
        }
        
        fputcsv($output, $headers);
        
        while ($row = $result->fetch_assoc()) {
            $filteredRow = [];
            foreach ($row as $key => $value) {
                if (strtolower($key) != 'id' && !preg_match('/_id$/i', $key)) {
                    $filteredRow[] = $value;
                }
            }
            fputcsv($output, $filteredRow);
        }
        
        rewind($output);
        $csvData = stream_get_contents($output);
        fclose($output);
        
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $tableName . '.csv"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo "\xEF\xBB\xBF" . $csvData;
        exit;
    }
    
    /**
     * Obtiene la lista de todas las tablas en la base de datos
     * 
     * @return array Lista de nombres de tablas
     */
    public function getAllTables() {
        $this->db->select_db($this->dbname);
        $tables = [];
        
        $result = $this->db->query("SHOW TABLES");
        if ($result) {
            while ($row = $result->fetch_row()) {
                $tables[] = $row[0];
            }
            $result->free();
        }
        
        return $tables;
    }

    /**
     * Cierra la conexión a la base de datos
     */
    public function closeConnection() {
        if ($this->db) {
            $this->db->close();
        }
    }
}
?>
