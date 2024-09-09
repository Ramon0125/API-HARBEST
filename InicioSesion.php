<?php

use Firebase\JWT\JWT;

header("Access-Control-Allow-Origin: *"); // Permite solicitudes desde cualquier origen
header("Access-Control-Allow-Methods: POST"); // Permite POST y OPTIONS
header("Access-Control-Allow-Headers: Content-Type"); 
header("Content-Type: application/json");

require './Tools/Funtions.php';

$Response['success'] = true;

// Obtener los datos JSON enviados desde la solicitud
$input = file_get_contents('php://input');
$data = json_decode($input, true); // Decodifica el JSON a un array PHP

// Verificar si los datos 'email' y 'pass' están presentes
if (!isset($data['email'], $data['pass'])) 
{
    $Response['success'] = false;
    $Response['EELS'] = true;
}

// Validar el formato de los datos
elseif (!ValidarCadenas($data['email'], $data['pass']))
{
    $Response['success'] = false;
    $Response['CNV'] = true;
} 

else {
    require './Controllers/Conexion.php';

    $Conexion = new ConexionDB();
    $ConexionT = $Conexion->GetConexion();

    $QueryStatement = "CALL SP_VALIDAR_LOGIN(?,?)";

    $QueryExecution = $ConexionT->prepare($QueryStatement);
    $QueryExecution->bindParam(1, $data['email']);
    $QueryExecution->bindParam(2, $data['pass']);
    $QueryExecution->execute();

    if ($QueryExecution->rowCount() === 0) { $Response['EELS'] = true; } 
    else {
        $UserData = $QueryExecution->fetch();

        if (isset($UserData['MENSAJE'])) 
        {
            $Response['success'] = false;
            $Response['message'] = $UserData['MENSAJE'];
        } 
        else {
            $payload = [
                'iss' => $_ENV['APP_URL'], // Emisor del token
                'iat' => time(),         // Tiempo en que el token fue emitido
                'exp' => time() + 3600,  // Tiempo de expiración (1 hora)
                'UserData' => array(
                    'CClave' => $UserData['CClave'],
                    'IDUsuario' => $UserData['IDUsuario'],
                    'Nombres' => $UserData['Nombres'],
                    'Apellidos' => $UserData['Apellidos'],
                    'Privilegios' => $UserData['Privilegios']
                )
            ];

            $Response['TOKEN'] = JWT::encode($payload, $_ENV['JWT_KEY'], "HS256");
        }
    }
}

echo json_encode($Response);
