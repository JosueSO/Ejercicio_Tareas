<?php 

class TareaException extends Exception {}

class Tarea {
    private $_id;
    private $_titulo;
    private $_descripcion;
    private $_fecha_limite;
    private $_completada;
    private $_categoria_id;

    public function __construct($id, $titulo, $descripcion, $fecha_limite, $completada, $categoria_id) {
        $this->setID($id);
        $this->setTitulo($titulo);
        $this->setDescripcion($descripcion);
        $this->setFechaLimite($fecha_limite);
        $this->setCompletada($completada);
        $this->setCategoriaID($categoria_id);
    }

    public function getID() {
        return $this->_id;
    }

    public function getTitulo() {
        return $this->_titulo;
    }

    public function getDescripcion() {
        return $this->_descripcion;
    }

    public function getFechaLimite() {
        return $this->_fecha_limite;
    }

    public function getCompletada() {
        return $this->_completada;
    }

    public function getCategoriaID() {
        return $this->_categoria_id;
    }

    public function setID($id) {
        if ($id !== null && (!is_numeric($id) || $id <= 0 || $id >= 2147483647 || $this->_id !== null)) {
            throw new TareaException("Error en ID de tarea");
        }
        $this->_id = $id;
    }

    public function setTitulo($titulo) {
        if ($titulo === null || strlen($titulo) > 50 || strlen($titulo) < 1) {
            throw new TareaException("Error en título de tarea");
        }
        $this->_titulo = $titulo;
    }

    public function setDescripcion($descripcion) {
        if ($descripcion !== null && strlen($descripcion) > 150) {
            throw new TareaException("Error en descripción de tarea");
        }
        $this->_descripcion = $descripcion;
    }

    public function setFechaLimite($fecha_limite) {
        if ($fecha_limite !== null && date_format(date_create_from_format('Y-m-d H:i', $fecha_limite), 'Y-m-d H:i') !== $fecha_limite) {
            throw new TareaException("Error en fecha límite de tarea");
        }
        $this->_fecha_limite = $fecha_limite;
    }

    public function setCompletada($completada) {
        if (strtoupper($completada) !== 'SI' && strtoupper($completada) !== 'NO') {
            throw new TareaException("Error en campo completada de tarea");
        }
        $this->_completada = $completada;
    }
    
    public function setCategoriaID($categoria_id) {
        if (!is_numeric($categoria_id) || $categoria_id <= 0 || $categoria_id >= 2147483647) {
            throw new TareaException("Error en ID de categoria en tarea");
        }
        $this->_categoria_id = $categoria_id;
    }

    public function getArray() {
        $tarea = array();

        $tarea['id'] = $this->getID();
        $tarea['titulo'] = $this->getTitulo();
        $tarea['descripcion'] = $this->getDescripcion();
        $tarea['fecha_limite'] = $this->getFechaLimite();
        $tarea['completada'] = $this->getCompletada();
        $tarea['categoria_id'] = $this->getCategoriaID();

        return $tarea;
    }
}

?>