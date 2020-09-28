<?php
require __DIR__ . '\vendor\autoload.php';
include_once __DIR__ . '.\vendor\Firebase\php-jwt\src\JWK.php';

// use \Firebase\JWT\JWT;
// $key = "pro3-parcial";

require_once './clases/auto.php';
require_once './clases/usuario.php';
require_once './clases/precios.php';





$method = $_SERVER['REQUEST_METHOD'];
$path_info = $_SERVER['PATH_INFO'];

function getToken()
{
    $headers = getallheaders();
    if (isset($headers['token']))
        return $headers['token'];
    else return false;
}

$token = getToken();
switch ($method) {
    case 'GET':
        if (Usuario::isValidUser(getToken())) {
            switch ($path_info) {
                case '/ingreso':
                    if (isset($_GET['patente'])) {
                        // 7. (GET) ingreso: Se recibe una patente y se devuelven todos los datos del vehículo.
                        echo Auto::getAuto($_GET['patente']);
                    } else {
                        // 6. (GET) ingreso: Devuelve un listado con todos los vehículos estacionados ordenados por tipo.
                        $autos = Auto::getAutosOrdenados();
                        echo "Vehiculos ingresados: " . PHP_EOL;
                        foreach ($autos as $item) {
                            echo $item;
                        }
                    }
                    break;
                case '/importe/hora':
                    echo "$".Auto::importePorTipo(Precios::importePorTipo("hora"), "hora");
                break;
                case '/importe/estadia':
                    echo "$".Auto::importePorTipo(Precios::importePorTipo("estadia"), "estadia");
                break;
                case '/importe/mensual':
                    echo "$".Auto::importePorTipo(Precios::importePorTipo("mensual"), "mensual");
                break;
            }
        } else {
            echo "No autorizado...";
        }
        break;
    case 'POST':
        switch ($path_info) {
            case '/registro':
                // (POST) registro. Registrar un usuario con los siguientes datos: email, tipo de usuario y password.
                // El tipo de usuario puede ser admin o user. Validar que el mail no esté registrado previamente.
                $clave = $_POST['password'] != null ? password_hash($_POST['password'], PASSWORD_DEFAULT) : "";

                $usuario = new Usuario($_POST['email'],  $clave, $_POST['tipo']);

                $rta = Usuario::isUniqueAndSet($usuario);

                if ($rta[0]) {
                    Usuario::guardarJson($usuario);
                } else {
                    echo $rta[1];
                }
                break;
            case '/login':
                // 2. (POST) login: Los usuarios deberán loguearse y se les devolverá un token con email y tipo en caso de estar
                // registrados, caso contrario se informará el error.
                $rta = Usuario::login($_POST['email'],  $_POST['password']);
                if ($rta != false) {
                    echo $rta;
                } else {
                    echo "Usuario o clave Incorrectos";
                }
                break;
            case '/precio':
                // 3. (POST) precio: Solo los administradores podrán cargar el precio por hora, por estadía y mensual en el
                // archivo precios.xxx
                if (Usuario::isValidUser($token) && Usuario::isAdmin($token)) {

                    $precios = new Precios($_POST['precio_hora'], $_POST['precio_estadia'], $_POST['precio_mensual']);
                    $rta = Precios::isUniqueAndSet($precios);
                    if ($rta[0]) {
                        Precios::guardarJson($precios);
                    } else {
                        echo $rta[1];
                    }
                } else {
                    echo "No es administrador autorizado...";
                }
                break;
            case '/ingreso':
                // 4. (POST) ingreso: Sólo users. Se ingresara patente, fecha_ingreso (dia y hora), tipo (hora, estadia, mensual)
                // y el email del usuario que ingresó el auto y se guardará en el archivo autos.xxx.            //     if (Usuario::isAdmin($token)) {
                if (Usuario::isValidUser($token)) {

                    $auto = new Auto($_POST['patente'], $_POST['tipo'], Usuario::getEmail($token));
                    $rta = Auto::isUniqueAndSet($auto);
                    if ($rta[0]) {
                        Auto::guardarJson($auto);
                    } else {
                        echo $rta[1];
                    }
                } else {
                    echo "No autorizado...";
                }
                break;
                // case '/asignacion':
                //     // 5. (POST) asignacion: Recibe legajo del profesor, id de la materia y turno (manana o noche) y lo guarda en el
                //     // archivo materias-profesores. No se debe poder asignar el mismo legajo en el mismo turno y materia.
                //     if (Usuario::isAdmin(getToken())) {
                //         $asignacion = new Asignacion($_POST['legajoProfesor'], $_POST['idMateria'], $_POST['turno']);
                //         $rta = Asignacion::isUniqueAndSet($asignacion);
                //         if ($rta[0]) {
                //             Asignacion::guardarJson($asignacion);
                //         } else {
                //             echo $rta[1];
                //         }
                //     } else {
                //         echo "No autorizado...";
                //     }
                //     break;
            default:
                break;
        }
        break;
    default:
        break;
}
