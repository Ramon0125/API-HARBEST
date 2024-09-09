<?php

class ControllerInicioSesion extends ConexionDB 
{
  private $response;
  private $ConexionDB;

  
  public function __construct()
  {
    parent::__construct(); //Llamada al constructor de la clase padre en este caso conexion
    $this->ConexionDB = $this->obtenerConexion(); // Asignar la conexión a la base de datos a la propiedad ConexionDB
  }


  public function ValidarLogin(string $Email, string $Password) : array 
  {
    try
    {
    // Llamada al procedimiento almacenado para validar los datos ingresados
    $Query = "CALL SP_VALIDAR_LOGIN(?,?)";
    $QueryExecution = $this->ConexionDB->prepare($Query);
    $QueryExecution->bindParam(1,$Email);
    $QueryExecution->bindParam(2,$Password);
    $QueryExecution->execute();
    
       
    // Si la consulta no trae datos dispara error
    if ($QueryExecution->rowCount() === 0){return HandleError();}

    //Se obtienen los resultados de la consulta en forma de un array asociativo
    $UserData = $QueryExecution->fetch(); 
     
    //Evalua si existe el mensaje de error
    $this->response['success'] = !isset($UserData['MENSAJE']);

    //Si existe el mensaje de error cancela la operacion
    if (!$this->response['success']) {$this->response['message'] = $UserData['MENSAJE'];  SUMBLOCKUSER();}
     
    else 
    {    
      // Cargar clave y IV desde variables de entorno
      $encryption_key = base64_decode($_ENV['ENCRYPTION_KEY']);
      $iv = base64_decode($_ENV['ENCRYPTION_IV']);
  
      // Cifrar la identidad
      $cipher_method = 'AES-256-CBC';
      $encrypted_identity = openssl_encrypt($UserData['Token'], $cipher_method, $encryption_key, 0, $iv);
  
      // Asigna la cookie que determina la identidad del usuario
      setcookie('IDENTITY', base64_encode($encrypted_identity . '::' . base64_encode($iv)), 0, '/', '', false, true);
  
      $this->response['CCLAVE'] = $UserData['CClave'];
  
      // Acciones si es el primer login del usuario
      if ($this->response['CCLAVE'] === 'F') 
      {
        $_SESSION['LOG'] = true;
        setcookie('PASS', $Password, 0, '/', '', false, true);
      }
    }
    
    }catch(Exception $e) {error_log($e->getMessage());  return HandleError();}

    return $this->response; 
  }


  public function ModificarPassword(string $passwordn1) : array 
  {     
    try 
    {  
    // Llamada al procedimiento almacenado para modificar la clave del usuario
    $Query = "CALL SP_MODIFICAR_CLAVES_USUARIOS (?, ?, ?)";
    $QueryExecution = $this->ConexionDB->prepare($Query);
    $QueryExecution->bindValue(1, GetInfo('Email'));
    $QueryExecution->bindValue(2, $_COOKIE['PASS']);
    $QueryExecution->bindParam(3, $passwordn1);
    $QueryExecution->execute();

    // Si la consulta no trae datos dispara error
    if ($QueryExecution->rowCount() === 0) {return HandleError();}
  
    $UserData = $QueryExecution->fetch();
    $this->response['message'] = $UserData['MENSAJE'];
    $this->response['success'] = $this->response['message'] === 'CMC';

    //Si la respuesta no es CMC devuelve error
    if(!$this->response['success']){SUMBLOCKUSER();}
    
    else     
    {
      AUDITORIA(GetInfo('IDUsuario'),'MODIFICO SU CONTRASEÑA'); 
      unset($_SESSION['LOG']); 
      setcookie('PASS', '', time() - 3600, '/', '', false, true);
    }

    }catch(Exception $e) {error_log($e->getMessage());  return HandleError();}

    return $this->response;
  }

}