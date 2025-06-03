<?php
/**
 * Clase DBManager - Gestiona la conexión a la base de datos para el sitio de Muros del Nalón
 * 
 * @author Alejandro Fernández García - UO295813
 * @version 1.0
 */
class DBManager {
    private $server;
    private $user;
    private $pass;
    private $dbname;
    private $db;    
    
    /**
     * Constructor de la clase DBManager
     * Inicializa la conexión a la base de datos
     */    
    public function __construct() {
        $this->server = "localhost";
        $this->user = "DBUSER2025";
        $this->pass = "DBPWD2025";
        $this->dbname = "muros_nalon";

        $this->db = new mysqli($this->server, $this->user, $this->pass);

        if ($this->db->connect_error) {
            die("Error de conexión a la base de datos: " . $this->db->connect_error);
        }
    }

    /**
     * Crea la base de datos si no existe
     * Ejecuta el archivo SQL de inicialización solo si la base de datos se crea por primera vez
     */
    public function createDatabase() {
        // Comprobar si la base de datos ya existe
        $checkDbQuery = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$this->dbname}'";
        $result = $this->db->query($checkDbQuery);
        $dbExists = ($result && $result->num_rows > 0);
        
        if (!$dbExists) {
            // La base de datos no existe, la creamos
            $createDbQuery = "CREATE DATABASE IF NOT EXISTS {$this->dbname}";
            if ($this->db->query($createDbQuery) === TRUE) {
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
            if (!empty($query)) {
                if ($this->db->query($query) === FALSE) {
                    echo "Error al ejecutar consulta: " . $this->db->error . "<br>";
                }
            }
        }
    }
    
    /**
     * Importa datos desde un archivo CSV a una tabla específica
     * 
     * @param string $tableName Nombre de la tabla
     * @param string $csvFilePath Ruta al archivo CSV
     */
    public function importFromCSV($tableName, $csvFilePath) {
        if (!file_exists($csvFilePath)) {
            die("Archivo CSV no encontrado: " . $csvFilePath);
        }
    
        $file = fopen($csvFilePath, "r");
    
        $headers = fgetcsv($file);
        $columns = implode(",", $headers);
    
        while (($row = fgetcsv($file)) !== FALSE) {
            $escapedValues = array_map([$this->db, 'real_escape_string'], $row);
            $values = implode(",", array_fill(0, count($escapedValues), '?'));
    
            $checkQuery = "SELECT COUNT(*) AS count FROM {$tableName} WHERE ";
            $conditions = [];
    
            foreach ($headers as $index => $column) {
                if ($index < count($row)) {
                    $conditions[] = "{$column} = ?";
                }
            }
            $checkQuery .= implode(" AND ", $conditions);
    
            $stmt = $this->db->prepare($checkQuery);
            if ($stmt === false) {
                die("Error al preparar la consulta: " . $this->db->error);
            }
    
            $types = str_repeat('s', count($row));
            $stmt->bind_param($types, ...$escapedValues);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->fetch_assoc()['count'] > 0;
    
            if (!$exists) {
                $insertQuery = "INSERT INTO {$tableName} ({$columns}) VALUES ({$values})";
                $stmt = $this->db->prepare($insertQuery);
                if ($stmt === false) {
                    die("Error al preparar la consulta de inserción: " . $this->db->error);
                }
                $stmt->bind_param($types, ...$escapedValues);
                $stmt->execute();
            }
            $stmt->close();
        }
        fclose($file);
    }
    
    /**
     * Obtiene la conexión a la base de datos
     * 
     * @return mysqli Conexión a la base de datos
     */
    public function getConnection() {
        $this->server = "localhost";
        $this->user = "DBUSER2025";
        $this->pass = "DBPWD2025";
        $this->dbname = "muros_nalon";

        $this->db = new mysqli($this->server, $this->user, $this->pass, $this->dbname);

        if ($this->db->connect_error) {
            die("Error de conexión a la base de datos: " . $this->db->connect_error);
        }
        
        return $this->db;
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
