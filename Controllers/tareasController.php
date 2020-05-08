<?php

require_once('../Models/DB.php');
require_once('../Models/Tarea.php');

try {
    $connection = DB::getConnection();
    if (array_key_exists("categoria_id", $_GET)) {
        //Devolver por categoria
        $categoria_id = $_GET['categoria_id'];

        $query = $connection->prepare('SELECT * FROM tareas WHERE categoria_id = :categoria_id');
        $query->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
        $query->execute();

        $tareas = array();

        while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $tarea = new Tarea($row['id'], $row['titulo'], $row['descripcion'], $row['fecha_limite'], $row['completada'], $row['categoria_id']);

            $tareas[] = $tarea->getArray();
        }

        echo json_encode($tareas);
    }
    else{
        //Devolver todas
        $query = $connection->prepare('SELECT * FROM tareas');
        $query->execute();

        $tareas = array();

        while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $tarea = new Tarea($row['id'], $row['titulo'], $row['descripcion'], $row['fecha_limite'], $row['completada'], $row['categoria_id']);

            $tareas[] = $tarea->getArray();
        }

        echo json_encode($tareas);
    }
}
catch (PDOException $e){
    echo "Error DB " . $e;
}

?>