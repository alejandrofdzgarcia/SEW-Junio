<?php
/**
 * Clase que representa una Reserva turística
 * 
 * @author Alejandro Fernández García - UO295813
 * @version 1.0
 */
class Reserva {
    private $id;
    private $usuario_id;
    private $recurso_id;
    private $numero_personas;
    private $precio_total;
    private $estado;
    private $fecha_reserva;
    
    // Propiedades adicionales para evitar consultas adicionales
    private $recurso_nombre;
    private $recurso_descripcion;
    
    /**
     * Constructor de la clase
     */
    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->usuario_id = $data['usuario_id'] ?? null;
        $this->recurso_id = $data['recurso_id'] ?? null;
        $this->numero_personas = $data['numero_personas'] ?? null;
        $this->precio_total = $data['precio_total'] ?? null;
        $this->estado = $data['estado'] ?? 'pendiente';
        $this->fecha_reserva = $data['fecha_reserva'] ?? date('Y-m-d H:i:s');
        $this->recurso_nombre = $data['recurso_nombre'] ?? null;
        $this->recurso_descripcion = $data['recurso_descripcion'] ?? null;
    }
    
    // Getters y setters
    public function getId() {
        return $this->id;
    }
    
    public function getUsuarioId() {
        return $this->usuario_id;
    }
    
    public function getRecursoId() {
        return $this->recurso_id;
    }
    
    public function getNumeroPersonas() {
        return $this->numero_personas;
    }
    
    public function getPrecioTotal() {
        return $this->precio_total;
    }
    
    public function getEstado() {
        return $this->estado;
    }
    
    public function getFechaReserva() {
        return $this->fecha_reserva;
    }
    
    public function getRecursoNombre() {
        return $this->recurso_nombre;
    }
    
    public function getRecursoDescripcion() {
        return $this->recurso_descripcion;
    }
    
    // Métodos de la clase
    public function cancelar() {
        $this->estado = 'cancelada';
    }
    
    public function confirmar() {
        $this->estado = 'confirmada';
    }
    
    public function toArray() {
        return [
            'id' => $this->id,
            'usuario_id' => $this->usuario_id,
            'recurso_id' => $this->recurso_id,
            'numero_personas' => $this->numero_personas,
            'precio_total' => $this->precio_total,
            'estado' => $this->estado,
            'fecha_reserva' => $this->fecha_reserva,
            'recurso_nombre' => $this->recurso_nombre,
            'recurso_descripcion' => $this->recurso_descripcion
        ];
    }
}