<?php
/**
 * Gestor de reservas que centraliza toda la funcionalidad relacionada
 * con la gestión de reservas turísticas
 * 
 * @author Alejandro Fernández García - UO295813
 * @version 1.0
 */

require_once 'DBManager.php';
require_once 'Reserva.php';
require_once 'RecursoTuristico.php';

class ReservasManager {
    private $db;
    private $usuario_id;
    private $mensaje = '';
    private $error = '';
    private $recursos = [];
    private $presupuesto = null;
    private $reservas = [];

    /**
     * Constructor de la clase
     * 
     * @param int $usuario_id ID del usuario actual
     */
    public function __construct($usuario_id = null) {
        $this->usuario_id = $usuario_id;
        
        $dbManager = new DBManager();
        $this->db = $dbManager->getConnection();
        
        if ($this->usuario_id) {
            $this->cargarRecursos();
        }
    }
    
    /**
     * Destructor de la clase - cierra la conexión a la base de datos
     */
    public function __destruct() {
        if ($this->db && $this->db instanceof mysqli) {
            $dbManager = new DBManager();
            $dbManager->closeConnection();
        }
    }
    
    /**
     * Carga todos los recursos turísticos disponibles
     */
    private function cargarRecursos() {
        try {
            $query = "SELECT id, nombre, descripcion, precio, limite_ocupacion, fecha_hora_inicio, fecha_hora_fin FROM recursos_turisticos";
            $resultado = $this->db->query($query);
            
            if ($resultado && $resultado->num_rows > 0) {
                while ($fila = $resultado->fetch_assoc()) {
                    $this->recursos[] = new RecursoTuristico($fila);
                }
            }
        } catch (Exception $e) {
            $this->error = "Error al obtener recursos: " . $e->getMessage();
        }
    }
    
    /**
     * Genera un presupuesto para una posible reserva
     * 
     * @param int $recurso_id ID del recurso a reservar
     * @param int $numero_personas Número de personas para la reserva
     * @return bool Éxito de la operación
     */
    public function generarPresupuesto($recurso_id, $numero_personas) {
        if ($numero_personas <= 0) {
            $this->error = "El número de personas debe ser mayor que cero.";
            return false;
        }
        
        try {
            $recurso = $this->getRecursoPorId($recurso_id);
            
            if (!$recurso) {
                $this->error = "No se encontró el recurso solicitado.";
                return false;
            }
            
            if (!$recurso->tieneCapacidadSuficiente($numero_personas)) {
                $this->error = "El número de personas excede el límite de ocupación del recurso (" . $recurso->getLimiteOcupacion() . " personas).";
                return false;
            }
            
            $precio_total = $recurso->calcularPrecioTotal($numero_personas);
            $this->presupuesto = [
                'recurso_id' => $recurso_id,
                'recurso_nombre' => $recurso->getNombre(),
                'numero_personas' => $numero_personas,
                'precio_unitario' => $recurso->getPrecio(),
                'precio_total' => $precio_total
            ];
            
            return true;
        } catch (Exception $e) {
            $this->error = "Error al generar presupuesto: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Confirma y crea una nueva reserva
     * 
     * @param int $recurso_id ID del recurso
     * @param int $numero_personas Número de personas
     * @param float $precio_total Precio total de la reserva
     * @return bool Éxito de la operación
     */
    public function confirmarReserva($recurso_id, $numero_personas, $precio_total) {
        if (!$this->usuario_id) {
            $this->error = "Debes iniciar sesión para realizar una reserva.";
            return false;
        }
        
        try {
            $recurso = $this->getRecursoPorId($recurso_id);
            
            if (!$recurso || !$recurso->tieneCapacidadSuficiente($numero_personas)) {
                $this->error = "El número de personas excede el límite de ocupación del recurso.";
                return false;
            }
            
            // Crear objeto Reserva
            $reservaData = [
                'usuario_id' => $this->usuario_id,
                'recurso_id' => $recurso_id,
                'numero_personas' => $numero_personas,
                'precio_total' => $precio_total,
                'estado' => 'confirmada',  // Status is set to "confirmada" here
                'recurso_nombre' => $recurso->getNombre(),
                'recurso_descripcion' => $recurso->getDescripcion()
            ];
            
            $reserva = new Reserva($reservaData);
            
            // Guardar en la base de datos
            $query = "INSERT INTO reservas (usuario_id, recurso_id, numero_personas, precio_total, estado) 
                     VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $estado = $reserva->getEstado();  // This will be "confirmada"
            $stmt->bind_param('iiids', 
                $reserva->getUsuarioId(), 
                $reserva->getRecursoId(), 
                $reserva->getNumeroPersonas(), 
                $reserva->getPrecioTotal(),
                $estado
            );
            
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
    
    /**
     * Obtiene todas las reservas del usuario actual
     * 
     * @return array Lista de reservas del usuario
     */
    public function obtenerReservasUsuario() {
        if (!$this->usuario_id) {
            $this->error = "Debes iniciar sesión para ver tus reservas.";
            return [];
        }
        
        try {
            $query = "SELECT r.id, r.usuario_id, r.recurso_id, r.fecha_reserva, r.numero_personas, 
                           r.precio_total, r.estado,
                           rt.nombre as recurso_nombre, rt.descripcion as recurso_descripcion,
                           rt.fecha_hora_inicio, rt.fecha_hora_fin
                    FROM reservas r
                    JOIN recursos_turisticos rt ON r.recurso_id = rt.id
                    WHERE r.usuario_id = ?
                    ORDER BY r.fecha_reserva DESC";
                    
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $this->usuario_id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            $this->reservas = [];
            if ($resultado && $resultado->num_rows > 0) {
                while ($fila = $resultado->fetch_assoc()) {
                    $this->reservas[] = new Reserva($fila);
                }
            }
            
            return $this->reservas;
        } catch (Exception $e) {
            $this->error = "Error al obtener reservas: " . $e->getMessage();
            return [];
        }
    }
    
    /**
     * Cancela una reserva existente
     * 
     * @param int $reserva_id ID de la reserva a cancelar
     * @return bool Éxito de la operación
     */
    public function cancelarReserva($reserva_id) {
        if (!$this->usuario_id) {
            $this->error = "Debes iniciar sesión para cancelar una reserva.";
            return false;
        }
        
        try {
            // Verificar que la reserva pertenece al usuario actual
            if (!$this->esReservaDelUsuario($reserva_id)) {
                $this->error = "No se encontró la reserva o no tienes permiso para cancelarla.";
                return false;
            }

            // Obtener la reserva
            $reserva = $this->obtenerDetallesReserva($reserva_id);
            if (!$reserva) {
                $this->error = "No se pudo encontrar la reserva.";
                return false;
            }
            
            // No necesitamos crear un nuevo objeto Reserva, usamos el que ya tenemos
            $reserva->cancelar();

            // Actualizar en la base de datos
            $query = "UPDATE reservas SET estado = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $estado = $reserva->getEstado();
            $id = $reserva->getId();
            $stmt->bind_param('si', $estado, $id);
            
            if ($stmt->execute()) {
                $this->mensaje = "Reserva cancelada con éxito.";
                return true;
            } else {
                $this->error = "No se pudo cancelar la reserva. Inténtalo de nuevo.";
                return false;
            }
        } catch (Exception $e) {
            $this->error = "Error al cancelar la reserva: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Verifica si una reserva pertenece al usuario actual
     * 
     * @param int $reserva_id ID de la reserva a verificar
     * @return bool Si la reserva pertenece al usuario
     */
    public function esReservaDelUsuario($reserva_id) {
        try {
            $query = "SELECT id FROM reservas WHERE id = ? AND usuario_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ii', $reserva_id, $this->usuario_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return ($result->num_rows > 0);
        } catch (Exception $e) {
            $this->error = "Error al verificar la reserva: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Obtiene los detalles de una reserva específica
     * 
     * @param int $reserva_id ID de la reserva
     * @return Reserva|null Objeto Reserva o null si no se encuentra
     */
    public function obtenerDetallesReserva($reserva_id) {
        try {
            $query = "SELECT r.*, rt.nombre as recurso_nombre, rt.descripcion as recurso_descripcion
                      FROM reservas r
                      JOIN recursos_turisticos rt ON r.recurso_id = rt.id
                      WHERE r.id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $reserva_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $data = $result->fetch_assoc();
                return new Reserva($data);
            }
            
            return null;
        } catch (Exception $e) {
            $this->error = "Error al obtener detalles de la reserva: " . $e->getMessage();
            return null;
        }
    }
    
    /**
     * Obtiene un recurso específico por su ID
     *
     * @param int $id ID del recurso a obtener
     * @return RecursoTuristico|null El recurso encontrado o null si no existe
     */
    public function getRecursoPorId($id) {
        try {
            $query = "SELECT * FROM recursos_turisticos WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                return new RecursoTuristico($result->fetch_assoc());
            }
            
            return null;
        } catch (Exception $e) {
            $this->error = "Error al obtener el recurso: " . $e->getMessage();
            return null;
        }
    }
    
    // Getters
    
    /**
     * Obtiene los recursos turísticos disponibles
     * 
     * @return array Lista de objetos RecursoTuristico
     */
    public function getRecursos() {
        return $this->recursos;
    }
    
    /**
     * Obtiene el presupuesto actual
     * 
     * @return array|null Presupuesto actual o null si no existe
     */
    public function getPresupuesto() {
        return $this->presupuesto;
    }
    
    /**
     * Obtiene el mensaje de confirmación
     * 
     * @return string Mensaje de confirmación
     */
    public function getMensaje() {
        return $this->mensaje;
    }
    
    /**
     * Obtiene el mensaje de error
     * 
     * @return string Mensaje de error
     */
    public function getError() {
        return $this->error;
    }
    
    /**
     * Obtiene las reservas del usuario
     * 
     * @return array Lista de objetos Reserva
     */
    public function getReservas() {
        return $this->reservas;
    }
}