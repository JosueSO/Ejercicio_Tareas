<?php 

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
        $this->_id = $id;
    }

    public function setTitulo($titulo) {
        $this->_titulo = $titulo;
    }

    public function setDescripcion($descripcion) {
        $this->_descripcion = $descripcion;
    }

    public function setFechaLimite($fecha_limite) {
        $this->_fecha_limite = $fecha_limite;
    }

    public function setCompletada($completada) {
        $this->_completada = $completada;
    }
    
    public function setCategoriaID($categoria_id) {
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