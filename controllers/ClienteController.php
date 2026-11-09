<?php

class ClienteController{
    private $clienteModel;

    public function __construct($conexion) {
        $this->clienteModel = new Cliente($conexion);
    }

    public function todos(){
        $datos = $this->clienteModel->todos();
        echo json_encode($datos);
    }

    public function uno($id){
        $dato = $this->clienteModel->uno($id);
        if ($dato === null) {
            http_response_code(404);
            echo json_encode(["error" => "El cliente no existe"]);
            return;
        }
        echo json_encode($dato);
    }

    public function guardar($nombre, $apellido, $cedula, $telefono, $correo){
        $resultado = $this->clienteModel->guardar($nombre, $apellido, $cedula, $telefono, $correo);

        echo json_encode([
            "guardado" => $resultado
        ]);
    }

    public function actualizar($id, $nombre, $apellido, $cedula, $telefono, $correo){
        $resultado = $this->clienteModel->actualizar($id, $nombre, $apellido, $cedula, $telefono, $correo);

        echo json_encode([
            "actualizado" => $resultado
        ]);
    }

    public function eliminar($id){
        $resultado = $this->clienteModel->eliminar($id);

        echo json_encode([
            "eliminado" => $resultado
        ]);
    }

    public function creartabla(){
        $yaExistia = $this->clienteModel->existeTabla();
        $resultado = $this->clienteModel->creartabla();
        echo json_encode([
            "tabla_creada" => $resultado,
            "ya_existia" => $yaExistia
        ]);
    }
}
