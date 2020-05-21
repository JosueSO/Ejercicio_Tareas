<?php

require_once('../Models/DB.php');
require_once('../Models/Tarea.php');
require_once('../Models/Response.php');

try {
    $connection = DB::getConnection();
}
catch (PDOException $e){
    error_log("Error de conexión - " . $e);

    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Error en conexión a Base de datos");
    $response->send();
    exit();
}

if (array_key_exists("categoria_id", $_GET)) {
    //GET host/tareas/categoria_id={id}
    if($_SERVER['REQUEST_METHOD'] === 'GET')
    {
        //Devolver por categoria
        $categoria_id = $_GET['categoria_id'];
        if ($categoria_id == '' || !is_numeric($categoria_id)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("El id de categoría no puede estar vacío y debe ser numérico");
            $response->send();
            exit();
        }

        try {
            $query = $connection->prepare('SELECT id, titulo, descripcion, DATE_FORMAT(fecha_limite, "%Y-%m-%d %H:%i") fecha_limite, completada, categoria_id FROM tareas WHERE categoria_id = :categoria_id');
            $query->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            $tareas = array();

            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $tarea = new Tarea($row['id'], $row['titulo'], $row['descripcion'], $row['fecha_limite'], $row['completada'], $row['categoria_id']);

                $tareas[] = $tarea->getArray();
            }

            $returnData = array();
            $returnData['total_registros'] = $rowCount;
            $returnData['tareas'] = $tareas;

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->setToCache(true);
            $response->setData($returnData);
            $response->send();
            exit();
        }
        catch(TareaException $e){
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
            exit();
        }
        catch(PDOException $e) {
            error_log("Error en BD - " . $e);

            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Error en consulta de tareas");
            $response->send();
            exit();
        }
    }
    else {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Método no permitido");
        $response->send();
        exit();
    }
}
elseif (empty($_GET)) {
    //GET host/tareas
    if($_SERVER['REQUEST_METHOD'] === 'GET')
    {
        try {
            $query = $connection->prepare('SELECT id, titulo, descripcion, DATE_FORMAT(fecha_limite, "%Y-%m-%d %H:%i") fecha_limite, completada, categoria_id FROM tareas');
            $query->execute();

            $rowCount = $query->rowCount();

            $tareas = array();

            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $tarea = new Tarea($row['id'], $row['titulo'], $row['descripcion'], $row['fecha_limite'], $row['completada'], $row['categoria_id']);

                $tareas[] = $tarea->getArray();
            }

            $returnData = array();
            $returnData['total_registros'] = $rowCount;
            $returnData['tareas'] = $tareas;

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->setToCache(true);
            $response->setData($returnData);
            $response->send();
            exit();
        }
        catch(TareaException $e){
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
            exit();
        }
        catch(PDOException $e) {
            error_log("Error en BD - " . $e);

            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Error en consulta de tareas");
            $response->send();
            exit();
        }
    }
    else {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Método no permitido");
        $response->send();
        exit();
    }
}

?>