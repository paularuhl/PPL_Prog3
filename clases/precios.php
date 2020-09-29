<?php
require_once 'FileManager.php';

class Precios extends FileManager
{
    public $_hora;
    public $_estadia;
    public $_mensual;

    public function __construct($_hora, $_estadia, $_mensual)
    {
        if (!is_null($_hora) && is_string($_hora)) {
            $this->_hora = $_hora;
        }
        if (!is_null($_estadia) && is_string($_estadia)) {
            $this->_estadia = $_estadia;
        }
        if (!is_null($_mensual) && is_string($_mensual)) {
            $this->_mensual = $_mensual;
        }
    }

    public static function isUniqueAndSet($precio)
    {
        //RTA ES UN ARRAY QUE TIENE UN CAMPO QUE ES TRUE POR DEFAULT
        $rta = [true];
        if (
            !isset($precio->_hora) || !isset($precio->_mensual)
            || !isset($precio->_estadia) || empty($precio->_hora) 
            || empty($precio->_mensual) || empty($precio->_estadia)
        ) {
            //SI ALGUN CAMPO ESTÁ VACÍO ASIGNO MENSAJE AL SEGUNDO INDICE
            $rta = [false, "No se permiten campos vacíos"];
        }
        return $rta;
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
        return "Precio por hora: $this->_hora".PHP_EOL."Precio por Estadía: $this->_estadia".PHP_EOL."Precio mensual: $this->_estadia".PHP_EOL;
    }

    public static function guardarJson($precio)
    {
        parent::jsonWrite("./archivos/precios.json", $precio);
    }

    public static function leerJson()
    {
        $lista = parent::jsonRead("./archivos/precios.json");

        foreach ($lista as $datos) {
            echo "lista del json";
            if (count((array)$datos) == 3) {
                $precios = new Precios($datos->_hora, $datos->_estadia, $datos->_mensual);
                return $precios;
            }
        }
    }

    public static function importePorTipo($tipo){
        $precios = Precios::leerJson();
        var_dump($precios);
        switch($tipo){
            case "hora":
                return $precios->_hora;
            break;
            case"estadia":
                return $precios->_estadia;
            break;
            case "mensual":
                return $precios->_mensual;
            break;
        }
    }

}
