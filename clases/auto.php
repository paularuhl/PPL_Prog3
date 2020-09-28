<?php
require_once 'FileManager.php';

class Auto extends FileManager
{
    public $_patente;
    public $_fechaIngreso;
    public $_tipoEstadia;
    public $_emailUsuario;

    public function __construct($patente, $tipoEstadia, $emailUsuario, $fechaIngreso = 0)
    {
        if (!is_null($patente) && is_string($patente)) {
            $this->_patente = $patente;
        }
        if (!is_null($tipoEstadia) && is_string($tipoEstadia)) {
            $this->_tipoEstadia = $tipoEstadia;
        }
        if (!is_null($emailUsuario) && is_string($emailUsuario)) {
            $this->_emailUsuario = $emailUsuario;
        }
        if ($fechaIngreso == 0) {
            $this->_fechaIngreso = date('d/m/y H:i');
        } else {
            $this->_fechaIngreso = $fechaIngreso;
        }
    }

    public static function isUniqueAndSet($auto)
    {
        //RTA ES UN ARRAY QUE TIENE UN CAMPO QUE ES TRUE POR DEFAULT
        $rta = [true];
        if (
            isset($auto->_patente) && isset($auto->_fechaIngreso)
            && isset($auto->_tipoEstadia) && isset($auto->_emailUsuario)
            && !empty($auto->_patente) && !empty($auto->_fechaIngreso)
            && !empty($auto->_tipoEstadia) && !empty($auto->_emailUsuario)
        ) {
            $autos = Auto::leerJson();

            foreach ($autos as $value) {
                if ($value->_patente == $auto->_patente) {
                    //SI EL OBJETO SE REPITE ASIGNO MENSAJE AL SEGUNDO INDICE
                    $rta = [false, "Este auto ya está ingresado... No se guardó"];
                }
            }
        } else {
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
        return "Patente: $this->_patente | Tipo Estadía: $this->_tipoEstadia | Fecha Ingreso: $this->_fechaIngreso | Ingresado por: $this->_emailUsuario";
    }

    public static function guardarJson($auto)
    {
        $listaDeAutos = Auto::leerJson();

        array_push($listaDeAutos, $auto);

        parent::jsonWrite("./archivos/autos.json", $listaDeAutos);
    }

    public static function leerJson()
    {
        $lista = parent::jsonRead("./archivos/autos.json");
        $listaDeAutos = array();

        foreach ($lista as $datos) {

            if (count((array)$datos) == 4) {
                $auto = new Auto($datos->_patente, $datos->_tipoEstadia, $datos->_emailUsuario, $datos->_fechaIngreso);
                array_push($listaDeAutos, $auto);
            }
        }
        return $listaDeAutos;
    }

    public static function getAuto($patente)
    {
        $autos = Auto::leerJson();

        foreach ($autos as $auto) {
            if ($auto->_patente == $patente) {
                return $auto;
            }
        }
        return "Esa patente no está registrada";
    }

    public static function getAutosOrdenados()
    {
        $autos = Auto::leerJson();

        $autosOrdenados = array();

        foreach ($autos as $auto) {
            if ($auto->_tipoEstadia == "hora") {
                array_push($autosOrdenados, $auto);
            }
        }

        foreach ($autos as $auto) {
            if ($auto->_tipoEstadia == "estadia") {
                array_push($autosOrdenados, $auto);
            }
        }

        foreach ($autos as $auto) {
            if ($auto->_tipoEstadia == "mensual") {
                array_push($autosOrdenados, $auto);
            }
        }

        return $autosOrdenados;
    }

    public static function importePorTipo($valor, $tipo)
    {
        $autos = Auto::getAutosOrdenados();
        $total = 0;
        foreach ($autos as $auto) {
            if ($auto->_tipoEstadia == $tipo) {
                $total = $valor +  $total;
            }
        }
        return $total;
    }
}
