<?php

class Cliente{
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function crearTabla(){
        $cadena = "create table if not exists clientes(
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        apellido VARCHAR(100) NOT NULL,
        cedula VARCHAR(20) NOT NULL UNIQUE,
        telefono VARCHAR(20) NOT NULL,
        correo VARCHAR(150) NOT NULL)";

        return $this->conexion->query($cadena);
    }

    public function existeTabla(){
        $cadena = "SHOW TABLES LIKE 'clientes'";
        $resultado = $this->conexion->query($cadena);
        return $resultado->num_rows > 0;
    }

    public function todos(){
        $cadena = "Select * from clientes";
        $resultado = $this->conexion->query($cadena);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function uno($id){
        $cadena = "Select * from clientes where id = ?";
        $ejecutar = $this->conexion->prepare($cadena);
        $ejecutar->bind_param('i', $id);
        $ejecutar->execute();
        $resultado = $ejecutar->get_result();
        return $resultado->fetch_assoc();
    }

    public function guardar($nombre, $apellido, $cedula, $telefono, $correo){
        $cadena = "insert into clientes(nombre, apellido, cedula, telefono, correo) values(?,?,?,?,?)";
        $ejecutar = $this->conexion->prepare($cadena);
        $ejecutar->bind_param('sssss', $nombre, $apellido, $cedula, $telefono, $correo);
        return $ejecutar->execute();
    }

    public function actualizar($id, $nombre, $apellido, $cedula, $telefono, $correo){
        $cadena = "update clientes set nombre = ?, apellido = ?, cedula = ?, telefono = ?, correo = ? where id = ?";
        $ejecutar = $this->conexion->prepare($cadena);
        $ejecutar->bind_param('sssssi', $nombre, $apellido, $cedula, $telefono, $correo, $id);
        return $ejecutar->execute();
    }

    public function eliminar($id){
        $cadena = "delete from clientes where id = ?";
        $ejecutar = $this->conexion->prepare($cadena);
        $ejecutar->bind_param('i', $id);
        return $ejecutar->execute();
    }
}
