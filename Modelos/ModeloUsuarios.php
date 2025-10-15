<?php

namespace Modelos;

use Conect\Conexion;
use PDO;
use PDOException;

class ModeloUsuarios
{

    // MOSTRAR USUARIOS - VERSIÓN CORREGIDA
    public static function mdlMostrarUsuarios($tabla, $item, $valor)
    {

        error_log("=== MDLMOSTRARUSUARIOS EJECUTADO ===");
        error_log("Tabla: $tabla, Item: $item, Valor: $valor");

        if ($item != null) {
            try {
                // Conexión directa y simple
                $conexion = Conexion::conectar();
                $stmt = $conexion->prepare("SELECT * FROM $tabla WHERE $item = :valor");
                $stmt->bindParam(":valor", $valor, PDO::PARAM_STR);
                $stmt->execute();

                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

                error_log("Resultado consulta: " . ($resultado ? "ENCONTRADO" : "NULL"));
                if ($resultado) {
                    error_log("Usuario: " . $resultado['usuario']);
                }

                return $resultado;
            } catch (PDOException $e) {
                error_log("ERROR PDO: " . $e->getMessage());
                return null;
            }
        }

        return null;
    }

    // REGISTRO DE USUARIOS
    public static function mdlNuevoUsuario($tabla, $datos)
    {
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre, usuario, password, perfil, dni, email, foto, id_empresa) VALUES (:nombre, :usuario, :password, :perfil, :dni, :email, :foto, :id_empresa)");
        $stmt->bindParam(":nombre", $datos['nombre'], PDO::PARAM_STR);
        $stmt->bindParam(":usuario", $datos['usuario'], PDO::PARAM_STR);
        $stmt->bindParam(":password", $datos['password'], PDO::PARAM_STR);
        $stmt->bindParam(":perfil", $datos['perfil'], PDO::PARAM_STR);
        $stmt->bindParam(":dni", $datos['dni'], PDO::PARAM_STR);
        $stmt->bindParam(":email", $datos['email'], PDO::PARAM_STR);
        $stmt->bindParam(":foto", $datos['foto'], PDO::PARAM_STR);
        $stmt->bindParam(":id_empresa", $datos['id_sucursal'], PDO::PARAM_INT);

        return $stmt->execute() ? "ok" : "error";
    }

    //EDITAR USUARIOS
    public static function mdlEditarUsuario($tabla, $datos)
    {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, password = :password, perfil= :perfil, dni = :dni, email = :email, foto = :foto, id_empresa = :id_empresa WHERE usuario = :usuario");
        $stmt->bindParam(":nombre", $datos['nombre'], PDO::PARAM_STR);
        $stmt->bindParam(":password", $datos['password'], PDO::PARAM_STR);
        $stmt->bindParam(":perfil", $datos['perfil'], PDO::PARAM_STR);
        $stmt->bindParam(":dni", $datos['dni'], PDO::PARAM_STR);
        $stmt->bindParam(":email", $datos['email'], PDO::PARAM_STR);
        $stmt->bindParam(":foto", $datos['foto'], PDO::PARAM_STR);
        $stmt->bindParam(":id_empresa", $datos['id_sucursal'], PDO::PARAM_INT);
        $stmt->bindParam(":usuario", $datos['usuario'], PDO::PARAM_STR);

        return $stmt->execute() ? "ok" : "error";
    }

    // ACTUALIZAR USUARIO ESTADO
    public static function mdlActualizarUsuario($tabla, $item1, $valor1, $item2, $valor2)
    {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE $item2 = :$item2");
        $stmt->bindParam(":" . $item1, $valor1, PDO::PARAM_STR);
        $stmt->bindParam(":" . $item2, $valor2, PDO::PARAM_STR);
        return $stmt->execute() ? "ok" : "error";
    }

    // BORRAR USUARIO
    public static function mdlBorrarUsuario($tabla, $datos)
    {
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id=:id");
        $stmt->bindParam(":id", $datos, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }
}
