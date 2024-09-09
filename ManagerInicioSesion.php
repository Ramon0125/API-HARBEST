<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo']) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
{
  require '../Controllers/Conexion.php';
  require '../Controllers/ControllersBlocks.php';
  require '../Controllers/Functions.php';
  require '../Controllers/ControllerInicioSesion.php';

if (VALIDARBLOCK() === 'T') 
 {
  if(Validarcadena1($_POST))
  {
  $verificar = new ControllerInicioSesion();

  if ($_POST['tipo'] == "iniusr" && isset($_POST['email'],$_POST['password']))
  { $response = $verificar->ValidarLogin($_POST['email'],$_POST['password']); }

  else if ($_POST['tipo'] === "mdfpass" && isset($_POST['passwordn1']))
  { $response = $verificar->ModificarPassword($_POST['passwordn1']); }

  else {$response = HandleError();}
  }

  else {$response['CNV'] = true; SUMBLOCKUSER();}
}

else 
{
  $response['block'] = true;
}

  header('Content-Type: application/json');
  echo json_encode($response);
}

else { header("LOCATION: ./404"); }