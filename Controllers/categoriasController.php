<?php

require_once('../Models/DB.php');
require_once('../Models/Categoria.php');

try {
    $connection = DB::getConnection();

    $query = $connection->prepare('SELECT * FROM categorias');
    $query->execute();
    $categorias = array();

    while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $categoria = new Categoria($row['id'], $row['nombre']);

        $categorias[] = $categoria->getArray();
    }

    echo json_encode($categorias);
}
catch (PDOException $e){
    echo "Error DB " . $e;
}

?>