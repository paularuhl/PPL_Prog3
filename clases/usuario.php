<?php
require_once 'FileManager.php';

use \Firebase\JWT\JWT;

class Usuario extends FileManager
{
    public $_email;
    public $_password;
    public $_tipo;

    public function __construct($email, $password, $tipo)
    {
        if (!is_null($email) && is_string($email)) {
            $this->_email = $email;
        }
        if (!is_null($password) && is_string($password)) {
            $this->_password = $password;
        }
        if (!is_null($tipo) && is_string($tipo)) {
            $this->_tipo = $tipo;
        }
    }

    public static function isUniqueAndSet($user)
    {
        //RTA ES UN ARRAY QUE TIENE UN CAMPO QUE ES TRUE POR DEFAULT
        $rta = [true];

        if (
            isset($user->_email) && isset($user->_password)
            && isset($user->_tipo) && !empty($user->_password)
            && !empty($user->_email) && !empty($user->_tipo)
        ) {
            $usuarios = Usuario::leerJson();
            foreach ($usuarios as $value) {

                if ($value->_email == $user->_email) {
                    //SI EL OBJETO SE REPITE ASIGNO MENSAJE AL SEGUNDO INDICE
                    $rta = [false, "Email repetido... No se guardó"];
                }
            }
        } else {
            //SI ALGUN CAMPO ESTÁ VACÍO ASIGNO MENSAJE AL SEGUNDO INDICE
            $rta = [false, "No se permiten campos vacíos"];
        }

        return $rta;
    }

    public static function login($email, $password)
    {
        $encodeOk = false;
        $payload = array();
        $usuarios = Usuario::leerJson();

        foreach ($usuarios as $user) {
            if ($user->_email == $email && password_verify($password, $user->_password)) {
                $payload = array(
                    "email" => $user->_email,
                    "tipo" => $user->_tipo
                );
                $encodeOk = JWT::encode($payload, "primerparcial");
                break;
            }
        }
        return $encodeOk;
    }

    public static function isValidUser($token)
    {
        try {
            JWT::decode($token, "primerparcial", array('HS256'));
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    public static function isAdmin($token)
    {
        $decoded = JWT::decode($token, "primerparcial", array('HS256'));
        if ($decoded->tipo == "admin") {
            return true;
        }
    }
    
    public static function getEmail($token)
    {
        $decoded = JWT::decode($token, "primerparcial", array('HS256'));
        return $decoded->email;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __toString()
    {
        return $this->_email . '*' . $this->_tipo . PHP_EOL;
    }

    public static function guardarJson($usuario)
    {
        echo "aaaa";
        $listaDeUsuarios = Usuario::leerJson();

        array_push($listaDeUsuarios, $usuario);
        parent::jsonWrite("./archivos/users.json", $listaDeUsuarios);
    }

    public static function leerJson()
    {
        $lista = parent::jsonRead("./archivos/users.json");
        $listaDeUsuarios = array();

        foreach ($lista as $datos) {

            if (count((array)$datos) == 3) {
                $usuario = new Usuario($datos->_email, $datos->_password, $datos->_tipo);
                array_push($listaDeUsuarios, $usuario);
            }
        }
        return $listaDeUsuarios;
    }
}
