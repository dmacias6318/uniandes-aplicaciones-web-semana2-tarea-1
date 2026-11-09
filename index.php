<?php

header("Content-Type: application/json");

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/models/cliente.php";
require_once __DIR__ . "/controllers/ClienteController.php";

function obtenerDatosCliente()
{
    return [
        "nombre"   => is_string($_POST["nombre"] ?? "") ? trim($_POST["nombre"]) : "",
        "apellido" => is_string($_POST["apellido"] ?? "") ? trim($_POST["apellido"]) : "",
        "cedula"   => is_string($_POST["cedula"] ?? "") ? trim($_POST["cedula"]) : "",
        "telefono" => is_string($_POST["telefono"] ?? "") ? trim($_POST["telefono"]) : "",
        "correo"   => is_string($_POST["correo"] ?? "") ? trim($_POST["correo"]) : ""
    ];
}

function validarCliente($datos)
{
    $errores = [];

    if ($datos["nombre"] === "" || mb_strlen($datos["nombre"]) > 100) {
        $errores[] = "El nombre es requerido y debe tener máximo 100 caracteres.";
    }
    if ($datos["apellido"] === "" || mb_strlen($datos["apellido"]) > 100) {
        $errores[] = "El apellido es requerido y debe tener máximo 100 caracteres.";
    }
    if ($datos["cedula"] === "" || mb_strlen($datos["cedula"]) > 20) {
        $errores[] = "La cédula es requerida y debe tener máximo 20 caracteres.";
    }
    if (mb_strlen($datos["telefono"]) > 20) {
        $errores[] = "El teléfono debe tener máximo 20 caracteres.";
    }
    if ($datos["correo"] !== "" && !filter_var($datos["correo"], FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo no tiene un formato válido.";
    }

    return $errores;
}

try {
    $db = new Database();
    $conexion = $db->conectar();

    $controlador = new ClienteController($conexion);

    $accion = $_GET["accion"] ?? "todos";

    switch ($accion) {
        case "todos":
            $controlador->todos();
            break;

        case "uno":
            $id = filter_var($_GET["id"] ?? "", FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                http_response_code(400);
                echo json_encode(["error" => "El id es requerido y debe ser un número entero positivo."]);
                break;
            }
            $controlador->uno($id);
            break;

        case "guardar":
        case "actualizar":
            if ($_SERVER["REQUEST_METHOD"] != "POST") {
                http_response_code(405);
                echo json_encode(["error" => "El método no es permitido"]);
                break;
            }

            $id = null;
            if ($accion === "actualizar") {
                $id = filter_var($_POST["id"] ?? "", FILTER_VALIDATE_INT);
                if ($id === false || $id <= 0) {
                    http_response_code(400);
                    echo json_encode(["error" => "El id es requerido y debe ser un número entero positivo."]);
                    break;
                }
            }

            $datos = obtenerDatosCliente();
            $errores = validarCliente($datos);
            if (count($errores) > 0) {
                http_response_code(400);
                echo json_encode(["error" => implode(" ", $errores)]);
                break;
            }

            if ($accion === "guardar") {
                http_response_code(201);
                $controlador->guardar($datos["nombre"], $datos["apellido"], $datos["cedula"], $datos["telefono"], $datos["correo"]);
            } else {
                $controlador->actualizar($id, $datos["nombre"], $datos["apellido"], $datos["cedula"], $datos["telefono"], $datos["correo"]);
            }
            break;

        case "eliminar":
            if ($_SERVER["REQUEST_METHOD"] != "POST") {
                http_response_code(405);
                echo json_encode(["error" => "El método no es permitido"]);
                break;
            }
            $id = filter_var($_POST["id"] ?? "", FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                http_response_code(400);
                echo json_encode(["error" => "El id es requerido y debe ser un número entero positivo."]);
                break;
            }
            $controlador->eliminar($id);
            break;

        case "creartabla":
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $controlador->creartabla();
            } else {
                http_response_code(405);
                echo json_encode(["error" => "El método no es permitido"]);
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(["error" => "Acción no encontrada"]);
    }
} catch (mysqli_sql_exception $error) {
    http_response_code(500);
    error_log($error->getMessage());

    if ($error->getCode() == 1146) {
        $mensaje = "La tabla clientes no existe. Pulsa Crear tabla para comenzar.";
    } elseif ($error->getCode() == 1062) {
        http_response_code(409);
        $mensaje = "La cédula ya está registrada. Ingrese una cédula diferente.";
    } else {
        $mensaje = "No se pudo completar la operación en la base de datos.";
    }

    echo json_encode(["error" => $mensaje]);
} catch (RuntimeException $error) {
    http_response_code(500);
    error_log($error->getMessage());
    echo json_encode(["error" => "No se pudo conectar con la base de datos."]);
}
