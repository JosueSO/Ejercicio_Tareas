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
else if(array_key_exists("id_tarea", $_GET)){
    $id_tarea = $_GET['id_tarea'];

    if ($id_tarea == '' || !is_numeric($id_tarea)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("El id de la tarea no puede estar vacío y debe ser numérico");
        $response->send();
        exit();
    }

    if($_SERVER['REQUEST_METHOD'] === 'GET') {
        try {
        
            $query = $connection->prepare('SELECT id, titulo, descripcion, DATE_FORMAT(fecha_limite, "%Y-%m-%d %H:%i") fecha_limite, completada, categoria_id FROM tareas WHERE id = :id');
            $query->bindParam(':id', $id_tarea, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("No se encontró la tarea");
                $response->send();
                exit();
            }

            while($row = $query->fetch(PDO::FETCH_ASSOC)){
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
        catch (TareaException $e) {
            $response = new Response();
        
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
            exit();
        }
        catch (PDOException $e) {
            error_log("Error en DB - " . $ex, 0);
        
            $response = new Response();
        
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Error al obtener tarea");
            $response->send();
            exit();
        }
    }
    elseif($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        try {
            $query = $connection->prepare('DELETE FROM tareas WHERE id = :id');
            $query->bindParam(':id', $id_tarea, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
        
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("Tarea no encontrada");
                $response->send();
                exit();
            }

            $response = new Response();
        
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->addMessage("Tarea eliminada");
            $response->send();
            exit();
        }
        catch (PDOException $e) {
            error_log("Error en DB - ".$e, 0);
        
            $response = new Response();
        
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Error al eliminar tarea");
            $response->send();
            exit();
        }
    }
    elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'){
        try {
            if ($_SERVER['CONTENT_TYPE'] !== 'application/json'){
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage('Encabezado "Content type" no es JSON');
                $response->send();
                exit();
            }

            $patchData = file_get_contents('php://input');

            if (!$json_data = json_decode($patchData)) {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage('El cuerpo de la solicitud no es un JSON válido');
                $response->send();
                exit();
            }

            $actualiza_titulo = false;
            $actualiza_descripcion = false;
            $actualiza_fechaLimite = false;
            $actualiza_completada = false;
            $actualiza_categoriaId = false;

            $campos_query = "";

            if (isset($json_data->titulo)) {
                $actualiza_titulo = true;
                $campos_query .= "titulo = :titulo, ";
            }

            if (isset($json_data->descripcion)) {
                $actualiza_descripcion = true;
                $campos_query .= "descripcion = :descripcion, ";
            }

            if (isset($json_data->fecha_limite)) {
                $actualiza_fechaLimite = true;
                $campos_query .= 'fecha_limite = STR_TO_DATE(:fecha_limite, \'%Y-%m-%d %H:%i\'), ';
            }

            if (isset($json_data->completada)) {
                $actualiza_completada = true;
                $campos_query .= "completada = :completada, ";
            }

            if (isset($json_data->categoria_id)) {
                $actualiza_categoriaId = true;
                $campos_query .= "categoria_id = :categoria_id, ";
            }

            $campos_query = rtrim($campos_query, ", ");

            if ($actualiza_titulo === false && $actualiza_descripcion === false && $actualiza_fechaLimite === false && $actualiza_completada === false && $actualiza_categoriaId === false) {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("No hay campos para actualizar");
                $response->send();
                exit();
            }

            $query = $connection->prepare('SELECT id, titulo, descripcion, DATE_FORMAT(fecha_limite, "%Y-%m-%d %H:%i") fecha_limite, completada, categoria_id FROM tareas WHERE id = :id');
            $query->bindParam(':id', $id_tarea, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("No se encontró la tarea");
                $response->send();
                exit();
            }

            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $tarea = new Tarea($row['id'], $row['titulo'], $row['descripcion'], $row['fecha_limite'], $row['completada'], $row['categoria_id']);
            }

            $cadena_query = 'UPDATE tareas SET ' . $campos_query . ' WHERE id = :id';
            $query = $connection->prepare($cadena_query);

            if($actualiza_titulo === true) {
                $tarea->setTitulo($json_data->titulo);
                $up_titulo = $tarea->getTitulo();
                $query->bindParam(':titulo', $up_titulo, PDO::PARAM_STR);
            }

            if($actualiza_descripcion === true) {
                $tarea->setDescripcion($json_data->descripcion);
                $up_descripcion = $tarea->getDescripcion();
                $query->bindParam(':descripcion', $up_descripcion, PDO::PARAM_STR);
            }

            if($actualiza_fechaLimite === true) {
                $tarea->setFechaLimite($json_data->fecha_limite);
                $up_fechaLimite = $tarea->getFechaLimite();
                $query->bindParam(':fecha_limite', $up_fechaLimite, PDO::PARAM_STR);
            }

            if($actualiza_completada === true) {
                $tarea->setCompletada($json_data->completada);
                $up_completada = $tarea->getCompletada();
                $query->bindParam(':completada', $up_completada, PDO::PARAM_STR);
            }

            if($actualiza_categoriaId === true) {
                $tarea->setCategoriaID($json_data->categoria_id);
                $up_categoriaID = $tarea->getCategoriaID();
                $query->bindParam(':categoria_id', $up_categoriaID, PDO::PARAM_INT);
            }

            $query->bindParam(':id', $id_tarea, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Error al actualizar la tarea");
                $response->send();
                exit();
            }

            $query = $connection->prepare('SELECT id, titulo, descripcion, DATE_FORMAT(fecha_limite, "%Y-%m-%d %H:%i") fecha_limite, completada, categoria_id FROM tareas WHERE id = :id');
            $query->bindParam(':id', $id_tarea, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("No se encontró la tarea después de actulizar");
                $response->send();
                exit();
            }

            $tareas = array();

            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $tarea = new Tarea($row['id'], $row['titulo'], $row['descripcion'], $row['fecha_limite'], $row['completada'], $row['categoria_id']);

                $tareas[] = $tarea->getArray();
            }

            $returnData = array();
            $returnData['total_registros'] = $rowCount;
            $returnData['tareas'] = $tareas;

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->addMessage("Tarea actualizada");
            $response->setData($returnData);
            $response->send();
            exit();
        }
        catch(TareaException $e) {
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
            $response->addMessage("Error en BD al actualizar la tarea");
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
    elseif($_SERVER['REQUEST_METHOD'] === 'POST'){
        try {
            if ($_SERVER['CONTENT_TYPE'] !== 'application/json'){
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage('Encabezado "Content type" no es JSON');
                $response->send();
                exit();
            }

            $postData = file_get_contents('php://input');

            if (!$json_data = json_decode($postData)) {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage('El cuerpo de la solicitud no es un JSON válido');
                $response->send();
                exit();
            }

            if (!isset($json_data->titulo) || !isset($json_data->completada) || !isset($json_data->categoria_id)) {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                (!isset($json_data->titulo) ? $response->addMessage('El título es obligatorio') : false);
                (!isset($json_data->completada) ? $response->addMessage('El campo de completada es obligatorio') : false);
                (!isset($json_data->ticategoria_idtulo) ? $response->addMessage('La categoría es obligatoria') : false);
                $response->send();
                exit();
            }

            $tarea = new Tarea(
                null, 
                $json_data->titulo,
                (isset($json_data->descripcion) ? $json_data->descripcion : null),
                (isset($json_data->fecha_limite) ? $json_data->fecha_limite : null),
                $json_data->completada,
                $json_data->categoria_id
            );

            $titulo = $tarea->getTitulo();
            $descripcion = $tarea->getDescripcion();
            $fecha_limite = $tarea->getFechaLimite();
            $completada = $tarea->getCompletada();
            $categoria_id = $tarea->getCategoriaID();

            $query = $connection->prepare('INSERT INTO tareas (titulo, descripcion, fecha_limite, completada, categoria_id) VALUES (:titulo, :descripcion, STR_TO_DATE(:fecha_limite, \'%Y-%m-%d %H:%i\'), :completada, :categoria_id)');
            $query->bindParam(':titulo', $titulo, PDO::PARAM_STR);
            $query->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $query->bindParam(':fecha_limite', $fecha_limite, PDO::PARAM_STR);
            $query->bindParam(':completada', $completada, PDO::PARAM_STR);
            $query->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Error al crear la tarea");
                $response->send();
                exit();
            }

            $ultimo_ID = $connection->lastInsertId();

            $query = $connection->prepare('SELECT id, titulo, descripcion, DATE_FORMAT(fecha_limite, "%Y-%m-%d %H:%i") fecha_limite, completada, categoria_id FROM tareas WHERE id = :id');
            $query->bindParam(':id', $ultimo_ID, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Error al obtener tarea después de crearla");
                $response->send();
                exit();
            }

            $tareas = array();

            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $tarea = new Tarea($row['id'], $row['titulo'], $row['descripcion'], $row['fecha_limite'], $row['completada'], $row['categoria_id']);

                $tareas[] = $tarea->getArray();
            }

            $returnData = array();
            $returnData['total_registros'] = $rowCount;
            $returnData['tareas'] = $tareas;

            $response = new Response();
            $response->setHttpStatusCode(201);
            $response->setSuccess(true);
            $response->addMessage("Tarea creada");
            $response->setData($returnData);
            $response->send();
            exit();
        }
        catch (TareaException $e) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
            exit();
        }
        catch (PDOException $e){
            error_log("Error en BD - " . $e);

            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Error en creación de tareas");
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