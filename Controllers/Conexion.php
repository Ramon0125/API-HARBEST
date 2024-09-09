<?php

if (preg_match('/Conexion(?:\.php)?/', $_SERVER['REQUEST_URI'])) 
{ 
    http_response_code(404);
    exit;
}

require (file_exists('../vendor/autoload.php') ? '..' : '.').'/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

use \Firebase\JWT\JWT;



class ConexionDB 
{
    private $conexion;

    public function __construct() 
    {
      try 
      { 
        $dsn = 'mysql:host='.$_ENV['DB_HOST'].';dbname='.$_ENV['DB_NAME'];
        $usuario = $_ENV['DB_USER'];
        $clave = $_ENV['DB_PASS'];
        $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'",
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
        $this->conexion = new PDO($dsn, $usuario, $clave, $options); 
      }
      catch (Exception $e) { error_log("Error en la conexión a la base de datos: " . $e->getMessage()); }
    }

    public function GetConexion(){ return $this->conexion;  }

}