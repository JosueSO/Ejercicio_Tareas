<?php 

class Categoria {
    private $_id;
    private $_nombre;

    public function __construct($id, $nombre) {
        $this->setID($id);
        $this->setNombre($nombre);
    }

    public function getID() {
        return $this->_id;
    }

    public function getNombre() {
        return $this->_nombre;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setNombre($nombre) {
        $this->_nombre = $nombre;
    }

    public function getArray() {
        $categoria = array();

        $categoria['id'] = $this->getID();
        $categoria['nombre'] = $this->getNombre();

        return $categoria;
    }
}

?>