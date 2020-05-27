<?php 

require_once('../Models/DB.php');
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

if (array_key_exists('id_sesion', $_GET)) {
    Echo 'algo';
}
elseif (empty($_GET)) {
    if($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Método no permitido");
        $response->send();
        exit();
    }

    if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Encabezado Content Type no es JSON");
        $response->send();
        exit();
    }

    $postData = file_get_contents('php://input');

    if(!$jsonData = json_decode($postData)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Cuerpo de la solicitud no es un JSON válido");
        $response->send();
        exit();
    }

    if (!isset($jsonData->nombre_usuario) || !isset($jsonData->contrasena)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->nombre_usuario) ? $response->addMessage("El nombre de usuario es obligatorio") : false);
        (!isset($jsonData->contrasena) ? $response->addMessage("La contraseña es obligatoria") : false);
        $response->send();
        exit();
    }

    try {
        $nombre_usuario = $jsonData->nombre_usuario;
        $contrasena = $jsonData->contrasena;
    
        $query = $connection->prepare('SELECT id, nombre_completo, contrasena, activo FROM usuarios WHERE nombre_usuario = :nombre_usuario');
        $query->bindParam(':nombre_usuario', $nombre_usuario, PDO::PARAM_STR);
        $query->execute();

        $rowCount = $query->rowCount();

        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("Nombre de usuario o contraseña incorrectos");
            $response->send();
            exit();
        }

        $row = $query->fetch(PDO::FETCH_ASSOC);

        $consulta_id = $row['id'];
        $consulta_nombreCompleto = $row['nombre_completo'];
        $consulta_contasena = $row['contrasena'];
        $consulta_activo = $row['activo'];

        if ($consulta_activo !== 'SI') {
            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("Nombre de usuario no activo");
            $response->send();
            exit();
        }

        if(!password_verify($contrasena, $consulta_contasena)) {
            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("Nombre de usuario o contraseña incorrectos");
            $response->send();
            exit();
        }

        $token_acceso = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)) . time());
        $token_actualizacion = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)) . time());

        $caducidad_tacceso_s = 1200;
        $caducidad_tactualizacion_s = 1296000;
    }
    catch(PDOException $e){
        error_log('Error en DB - ' . $e);

        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Error al iniciar sesión");
        $response->send();
        exit();
    }

    try{
        $connection->beginTransaction();

        $query = $connection->prepare('INSERT INTO sesiones(id_usuario, token_acceso, caducidad_token_acceso, token_actualizacion, caducidad_token_actualizacion) VALUES (:id_usuario, :token_acceso, DATE_ADD(NOW(), INTERVAL :caducidad_tacceso_s SECOND), :token_actualizacion, DATE_ADD(NOW(), INTERVAL :caducidad_tactualizacion_s SECOND))');
        $query->bindParam(':id_usuario', $consulta_id, PDO::PARAM_INT);
        $query->bindParam(':token_acceso', $token_acceso, PDO::PARAM_STR);
        $query->bindParam(':caducidad_tacceso_s', $caducidad_tacceso_s, PDO::PARAM_INT);
        $query->bindParam(':token_actualizacion', $token_actualizacion, PDO::PARAM_STR);
        $query->bindParam(':caducidad_tactualizacion_s', $caducidad_tactualizacion_s, PDO::PARAM_INT);
        $query->execute();

        $ultimoID = $connection->lastInsertId();

        $connection->commit();

        $returnData = array();
        $returnData['id_sesion'] = intval($ultimoID);
        $returnData['token_acceso'] = $token_acceso;
        $returnData['caducidad_token_acceso'] = $caducidad_tacceso_s;
        $returnData['token_actualizacion'] = $token_actualizacion;
        $returnData['caducidad_token_actualizacion'] = $caducidad_tactualizacion_s;

        $response = new Response();
        $response->setHttpStatusCode(201);
        $response->setSuccess(true);
        $response->setData($returnData);
        $response->send();
        exit();
    }
    catch(PDOException $e) {
        $connection->rollBack();

        error_log('Error en DB - ' . $e);

        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Error al iniciar sesión");
        $response->send();
        exit();
    }
    echo 'listo';
}
else{
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Ruta no encontrada");
    $response->send();
    exit();
}

?>