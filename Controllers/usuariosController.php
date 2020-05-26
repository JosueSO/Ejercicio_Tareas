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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

if (!$json_data = json_decode($postData)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("El cuerpo de la solicitud no es un JSON válido");
    $response->send();
    exit();
}

if (!isset($json_data->nombre_completo) || !isset($json_data->nombre_usuario) || !isset($json_data->contrasena)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    (!isset($json_data->nombre_completo) ? $response->addMessage("El nombre completo es obligatorio") : false);
    (!isset($json_data->nombre_usuario) ? $response->addMessage("El nombre de usuario es obligatorio") : false);
    (!isset($json_data->contrasena) ? $response->addMessage("La contraseña es obligatoria") : false);
    $response->send();
    exit();
}

//Validación de longitud....

$nombre_completo = trim($json_data->nombre_completo);
$nombre_usuario = trim($json_data->nombre_usuario);
$contrasena = $json_data->contrasena;

try {
    $query = $connection->prepare('SELECT id FROM usuarios WHERE nombre_usuario = :nombre_usuario');
    $query->bindParam(':nombre_usuario', $nombre_usuario, PDO::PARAM_STR);
    $query->execute();

    $rowCount = $query->rowCount();

    if($rowCount !== 0) {
        $response = new Response();
        $response->setHttpStatusCode(409);
        $response->setSuccess(false);
        $response->addMessage("El nombre de usuario ya existe");
        $response->send();
        exit();
    }

    $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);

    $query = $connection->prepare('INSERT INTO usuarios(nombre_completo, nombre_usuario, contrasena) VALUES(:nombre_completo, :nombre_usuario, :contrasena)');
    $query->bindParam(':nombre_completo', $nombre_completo, PDO::PARAM_STR);
    $query->bindParam(':nombre_usuario', $nombre_usuario, PDO::PARAM_STR);
    $query->bindParam(':contrasena', $contrasena_hash, PDO::PARAM_STR);
    $query->execute();

    $rowCount = $query->rowCount();

    if($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Error al crear usuario - inténtelo de nuevo");
        $response->send();
        exit();
    }

    $ultimoID = $connection->lastInsertId();

    $returnData = array();
    $returnData['id_usuario'] = $ultimoID;
    $returnData['nombre_completo'] = $nombre_completo;
    $returnData['nombre_usuario'] = $nombre_usuario;

    $response = new Response();
    $response->setHttpStatusCode(201);
    $response->setSuccess(true);
    $response->addMessage("Usuario creado");
    $response->setData($returnData);
    $response->send();
    exit();
}
catch(PDOException $e) {
    error_log('Error en BD - ' . $e);

    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Error al crear usuario");
    $response->send();
    exit();
}


?>