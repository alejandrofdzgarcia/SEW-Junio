<?php
/**
 * Clase que representa un Recurso Turístico
 * 
 * @author Alejandro Fernández García - UO295813
 * @version 1.0
 */
class RecursoTuristico {
    private $id;
    private $nombre;
    private $descripcion;
    private $precio;
    private $limite_ocupacion;
    private $fecha_hora_inicio;
    private $fecha_hora_fin;
    
    /**
     * Constructor de la clase
     */
    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->nombre = $data['nombre'] ?? null;
        $this->descripcion = $data['descripcion'] ?? null;
        $this->precio = $data['precio'] ?? 0;
        $this->limite_ocupacion = $data['limite_ocupacion'] ?? 0;
        $this->fecha_hora_inicio = $data['fecha_hora_inicio'] ?? null;
        $this->fecha_hora_fin = $data['fecha_hora_fin'] ?? null;
    }
    
    // Getters
    public function getId() {
        return $this->id;
    }
    
    public function getNombre() {
        return $this->nombre;
    }
    
    public function getDescripcion() {
        return $this->descripcion;
    }
    
    public function getPrecio() {
        return $this->precio;
    }
    
    public function getLimiteOcupacion() {
        return $this->limite_ocupacion;
    }
    
    public function getFechaHoraInicio() {
        return $this->fecha_hora_inicio;
    }
    
    public function getFechaHoraFin() {
        return $this->fecha_hora_fin;
    }
    
    /**
     * Calcula el precio total para un número determinado de personas
     * 
     * @param int $numero_personas Número de personas
     * @return float Precio total
     */
    public function calcularPrecioTotal($numero_personas) {
        return $this->precio * $numero_personas;
    }
    
    /**
     * Verifica si hay capacidad suficiente para el número de personas indicado
     * 
     * @param int $numero_personas Número de personas a verificar
     * @return bool True si hay capacidad suficiente, false en caso contrario
     */
    public function tieneCapacidadSuficiente($numero_personas) {
        return $numero_personas <= $this->limite_ocupacion;
    }
    
    /**
     * Convierte el objeto a un array asociativo
     * 
     * @return array Datos del recurso turístico
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'limite_ocupacion' => $this->limite_ocupacion,
            'fecha_hora_inicio' => $this->fecha_hora_inicio,
            'fecha_hora_fin' => $this->fecha_hora_fin
        ];
    }
}