<?php
namespace Conect;

 date_default_timezone_set('America/Lima'); 
 class Conexion{
      
        const HOST = '127.0.0.1';
        const USER = 'root';
        const PASSWORD = 'root';
<<<<<<< HEAD
        const BDNAME = 'tiendasantiagoeirl_php';
=======
        const BDNAME = 'tiendasantiagoeirl';
>>>>>>> 9439536e0268cfd2c3cc7bc7bc06083e7ba7a236
    public static function conectar() {
       
        $link = new \PDO("mysql:host=".self::HOST."; dbname=".self::BDNAME.";",
                        self::USER, self::PASSWORD);
        $link->exec("set names utf8");
        return $link;
    }
}
