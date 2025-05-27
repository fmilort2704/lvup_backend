<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require 'Firebase/autoload.php';

define("SERVIDOR_BD","localhost");
define("USUARIO_BD","root");
define("CLAVE_BD","");
define("NOMBRE_BD","bd_lvup");
define("PASSWORD_API","PASSWORD_DE_MI_APLICACION");

function validateToken()
{
    $headers = apache_request_headers();
    if (!isset($headers["Authorization"]))
        return false; // Sin autorización
    else {
        $authorization = $headers["Authorization"];
        $authorizationArray = explode(" ", $authorization);
        $token = $authorizationArray[1];
        try {
            $info = JWT::decode($token, new Key(PASSWORD_API, 'HS256'));
        } catch (\Throwable $th) {
            return false; // Expirado
        }

        try {
            $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
        } catch (PDOException $e) {
            $respuesta["error"] = "Imposible conectar:" . $e->getMessage();
            return $respuesta;
        }

        try {
            $consulta = "SELECT * FROM Usuarios WHERE id_usuario=?";
            $sentencia = $conexion->prepare($consulta);
            $sentencia->execute([$info->data]);
        } catch (PDOException $e) {
            $respuesta["error"] = "Imposible realizar la consulta:" . $e->getMessage();
            $sentencia = null;
            $conexion = null;
            return $respuesta;
        }

        if ($sentencia->rowCount() > 0) {
            $respuesta["usuario"] = $sentencia->fetch(PDO::FETCH_ASSOC);
            $payload['exp'] = time() + 3600;
            $payload['data'] = $respuesta["usuario"]["id_usuario"];
            $jwt = JWT::encode($payload, PASSWORD_API, 'HS256');
            $respuesta["token"] = $jwt;
        } else {
            $respuesta["mensaje_baneo"] = "El usuario no se encuentra registrado en la BD";
        }

        $sentencia = null;
        $conexion = null;
        return $respuesta;
    }
}

function login($usuario, $contrasenya)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        $respuesta["error"] = "Imposible conectar:" . $e->getMessage();
        return $respuesta;
    }

    try {
        $consulta = "SELECT * FROM usuarios WHERE email=? AND contrasenya=?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$usuario, md5($contrasenya)]);
    } catch (PDOException $e) {
        $respuesta["error"] = "Imposible realizar la consulta:" . $e->getMessage();
        $sentencia = null;
        $conexion = null;
        return $respuesta;
    }

    if ($sentencia->rowCount() > 0) {
        $respuesta["usuario"] = $sentencia->fetch(PDO::FETCH_ASSOC);
        //$payload = ['exp' => time() + 3600, 'data' => $respuesta["usuario"]["id_usuario"]];
        //$jwt = JWT::encode($payload, PASSWORD_API, 'HS256');
        //$respuesta["token"] = $jwt;
    } else {
        $respuesta["mensaje"] = "El usuario no se encuentra registrado en la BD";
    }

    $sentencia = null;
    $conexion = null;
    return $respuesta;
}

function crear_producto($nombre, $descripcion, $estado, $precio, $stock, $imagen_url, $categoria_id, $vendedor_id)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "INSERT INTO productos (nombre, descripcion, estado, precio, stock, imagen_url, fecha_publicacion, categoria_id, vendedor_id) 
                     VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$nombre, $descripcion, $estado, $precio, $stock, $imagen_url, $categoria_id, $vendedor_id]);
    } catch (PDOException $e) {
        $sentencia = null;
        $conexion = null;
        return ["error" => "Error al crear el producto: " . $e->getMessage()];
    }

    $sentencia = null;
    $conexion = null;
    return ["mensaje" => "Producto creado correctamente"];
}

function obtener_productos()
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "SELECT * FROM productos";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute();
        $productos = $sentencia->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ["error" => "Imposible realizar la consulta: " . $e->getMessage()];
    }

    return ["productos" => $productos];
}

function obtener_producto_por_id($id_producto)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "SELECT * FROM productos WHERE id_producto = ?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$id_producto]);

        if ($sentencia->rowCount() > 0) {
            return ["producto" => $sentencia->fetch(PDO::FETCH_ASSOC)];
        } else {
            return ["mensaje" => "Producto con ID $id_producto no encontrado"];
        }
    } catch (PDOException $e) {
        return ["error" => "Imposible realizar la consulta: " . $e->getMessage()];
    }
}

function borrar_producto($id_producto)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "DELETE FROM productos WHERE id_producto = ?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$id_producto]);

        return ["mensaje" => "Producto con ID $id_producto borrado con exito"];
    } catch (PDOException $e) {
        return ["error" => "Imposible realizar la consulta: " . $e->getMessage()];
    }
}

function obtener_productos_por_categoria($id_categoria)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "SELECT * FROM productos WHERE categoria_id = ?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$id_categoria]);

        if ($sentencia->rowCount() > 0) {
            return ["productos" => $sentencia->fetchALL(PDO::FETCH_ASSOC)];
        } else {
            return ["mensaje" => "No se han encontrado productos con id de categoria ".$id_categoria];
        }
    } catch (PDOException $e) {
        return ["error" => "Imposible realizar la consulta: " . $e->getMessage()];
    }
}

function obtener_productos_por_usuario($id_usuario)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "SELECT * FROM productos WHERE vendedor_id = ?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$id_usuario]);

        if ($sentencia->rowCount() > 0) {
            return ["productos" => $sentencia->fetchALL(PDO::FETCH_ASSOC)];
        } else {
            return ["mensaje" => "No se han encontrado productos asociados al usuario con id ".$id_categoria];
        }
    } catch (PDOException $e) {
        return ["error" => "Imposible realizar la consulta: " . $e->getMessage()];
    }
}

function registrar_usuario($nombre, $email, $contrasenya)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "INSERT INTO usuarios (nombre, email, contrasenya, fecha_registro) 
                     VALUES (?, ?, ?, NOW())";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$nombre, $email, md5($contrasenya)]);
    } catch (PDOException $e) {
        $sentencia = null;
        $conexion = null;
        return ["error" => "Error al registrar al usuario: " . $e->getMessage()];
    }

    $sentencia = null;
    $conexion = null;
    return ["mensaje" => "Usuario resgistrado correctamente"];
}

function obtener_usuario($id_usuario)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        $respuesta["error"] = "Imposible conectar:" . $e->getMessage();
        return $respuesta;
    }

    try {
        $consulta = "SELECT nombre, email, rol, puntos, verificado FROM usuarios WHERE id_usuario=?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$id_usuario]);
    } catch (PDOException $e) {
        $respuesta["error"] = "Imposible realizar la consulta:" . $e->getMessage();
        $sentencia = null;
        $conexion = null;
        return $respuesta;
    }

    if ($sentencia->rowCount() > 0) {
        $respuesta["usuario"] = $sentencia->fetch(PDO::FETCH_ASSOC);
    } else {
        $respuesta["mensaje"] = "Usuario con ".$id_usuario." no encontrado";
    }

    $sentencia = null;
    $conexion = null;
    return $respuesta;
}

/*function actualizar_usuario($id_usuario, $nombre, $email, $contrasenia, $rol, $verificado, $puntos)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "UPDATE usuarios 
                     SET nombre = ?, email = ?, contrasenya = ?, rol = ?, verificado = ?, puntos = ? 
                     WHERE id_usuario = ?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$nombre, $email, md5($contrasenia), $rol, $verificado, $puntos, $id_usuario]);
    } catch (PDOException $e) {
        $sentencia = null;
        $conexion = null;
        return ["error" => "Error al actualizar el usuario: " . $e->getMessage()];
    }

    $sentencia = null;
    $conexion = null;
    return ["mensaje" => "Usuario actualizado correctamente"];
}*/

function actualizar_usuario($id_usuario, $nombre, $email, $contrasenia)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "UPDATE usuarios 
                     SET nombre = ?, email = ?, contrasenya = ? 
                     WHERE id_usuario = ?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$nombre, $email, md5($contrasenia), $id_usuario]);
    } catch (PDOException $e) {
        $sentencia = null;
        $conexion = null;
        return ["error" => "Error al actualizar el usuario: " . $e->getMessage()];
    }

    $sentencia = null;
    $conexion = null;
    return ["mensaje" => "Usuario actualizado correctamente"];
}

function eliminar_usuario($id_usuario)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "DELETE FROM usuarios WHERE id_usuario = ?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$id_usuario]);

        return ["mensaje" => "Usario con ID $id_usuario borrado con exito"];
    } catch (PDOException $e) {
        return ["error" => "Imposible realizar la consulta: " . $e->getMessage()];
    }
}

function obtener_posts()
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "SELECT posts.*, usuarios.nombre FROM posts join usuarios on usuarios.id_usuario = posts.autor_id;";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute();
        $posts = $sentencia->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ["error" => "Imposible realizar la consulta: " . $e->getMessage()];
    }

    return ["posts" => $posts];
}

function obtener_post($id_post)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        $respuesta["error"] = "Imposible conectar:" . $e->getMessage();
        return $respuesta;
    }

    try {
        $consulta = "SELECT posts.*, usuarios.nombre FROM posts join usuarios on usuarios.id_usuario = posts.autor_id WHERE id_post=?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$id_post]);
    } catch (PDOException $e) {
        $respuesta["error"] = "Imposible realizar la consulta:" . $e->getMessage();
        $sentencia = null;
        $conexion = null;
        return $respuesta;
    }

    if ($sentencia->rowCount() > 0) {
        $respuesta["post"] = $sentencia->fetch(PDO::FETCH_ASSOC);
    } else {
        $respuesta["mensaje"] = "Usuario con ".$id_usuario." no encontrado";
    }

    $sentencia = null;
    $conexion = null;
    return $respuesta;
}

function crear_post($titulo, $descripcion, $comentario, $imagen_url, $autor_id)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "INSERT INTO posts (titulo, descripcion, comentario, fecha, img_publicacion, autor_id) 
                     VALUES (?, ?, ?, NOW(), ?, ?)";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$titulo, $descripcion, $comentario, $imagen_url, $autor_id]);
    } catch (PDOException $e) {
        $sentencia = null;
        $conexion = null;
        return ["error" => "Error al crear el producto: " . $e->getMessage()];
    }

    $sentencia = null;
    $conexion = null;
    return ["mensaje" => "Post creado correctamente"];
}

function actualizar_post($id_post, $titulo, $comentario, $autor_id)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "UPDATE posts 
                     SET titulo = ?, comentario = ?, autor_id = ?
                     WHERE id_post = ?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$titulo, $comentario, $autor_id, $id_post]);
    } catch (PDOException $e) {
        $sentencia = null;
        $conexion = null;
        return ["error" => "Error al actualizar el post: " . $e->getMessage()];
    }

    $sentencia = null;
    $conexion = null;
    
    return ["mensaje" => "Post actualizado correctamente"];
}

function eliminar_post($id_post)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "DELETE FROM posts WHERE id_post = ?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$id_post]);

        return ["mensaje" => "Post con ID $id_post borrado con exito"];
    } catch (PDOException $e) {
        return ["error" => "Imposible realizar la consulta: " . $e->getMessage()];
    }
}

function obtener_post_por_usuario($id_usuario)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "SELECT * FROM posts WHERE autor_id = ?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$id_usuario]);

        if ($sentencia->rowCount() > 0) {
            return ["posts" => $sentencia->fetchALL(PDO::FETCH_ASSOC)];
        } else {
            return ["mensaje" => "No se han encontrado posts asociados al usuario con id ".$id_categoria];
        }
    } catch (PDOException $e) {
        return ["error" => "Imposible realizar la consulta: " . $e->getMessage()];
    }
}

function obtener_comentario_de_post($id_post)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "SELECT comentarios.*, usuarios.nombre FROM comentarios
        JOIN usuarios on comentarios.autor_id = usuarios.id_usuario
        WHERE post_id = ?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$id_post]);

        if ($sentencia->rowCount() > 0) {
            return ["comentarios" => $sentencia->fetchALL(PDO::FETCH_ASSOC)];
        } else {
            return ["mensaje" => "No se han encontrado comentarios asociados al post con id ".$id_post];
        }
    } catch (PDOException $e) {
        return ["error" => "Imposible realizar la consulta: " . $e->getMessage()];
    }
}

function crear_comentario($contenido, $post_id, $autor_id)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "INSERT INTO comentarios (contenido, fecha, post_id, autor_id) 
                     VALUES (?, NOW(), ?, ?)";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$contenido, $post_id, $autor_id]);
    } catch (PDOException $e) {
        $sentencia = null;
        $conexion = null;
        return ["error" => "Error al crear el comentario: " . $e->getMessage()];
    }

    $sentencia = null;
    $conexion = null;
    return ["mensaje" => "Comentario creado correctamente"];
}

function eliminar_comentario($id_comentario)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "DELETE FROM comentarios WHERE id_comentario = ?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$id_comentario]);

        return ["mensaje" => "Comentario con ID $id_comentario borrado con exito"];
    } catch (PDOException $e) {
        return ["error" => "Imposible realizar la consulta: " . $e->getMessage()];
    }
}

function aniadir_al_carrito($usuario_id, $producto_id)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "INSERT INTO carrito (usuario_id, producto_id, cantidad, fecha_anyadido) 
                     VALUES (?, ?, 1, NOW())
                     ON DUPLICATE KEY UPDATE
                    cantidad = cantidad + VALUES(cantidad);";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$usuario_id, $producto_id]);
    } catch (PDOException $e) {
        $sentencia = null;
        $conexion = null;
        return ["error" => "Error al crear el comentario: " . $e->getMessage()];
    }

    $sentencia = null;
    $conexion = null;
    return ["mensaje" => "Producto añadido al carrito correctamente"];
}

function obtener_productos_carrito($usuario_id)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "SELECT carrito.*, productos.nombre, productos.precio, productos.imagen_url
        FROM carrito 
        JOIN productos on carrito.producto_id = productos.id_producto
        WHERE carrito.usuario_id = ?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$usuario_id]);

        if ($sentencia->rowCount() > 0) {
            return ["carrito" => $sentencia->fetchALL(PDO::FETCH_ASSOC)];
        } else {
            return ["mensaje" => "No se han encontrado productos en el carrito del usuario con id ".$id_post];
        }
    } catch (PDOException $e) {
        return ["error" => "Imposible realizar la consulta: " . $e->getMessage()];
    }
}

function eliminar_producto_carrito($id_producto)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "DELETE FROM carrito WHERE producto_id = ?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$id_producto]);

        return ["mensaje" => "Producto con ID $id_producto borrado con exito del carrito"];
    } catch (PDOException $e) {
        return ["error" => "Imposible realizar la consulta: " . $e->getMessage()];
    }
}

function incrementar_cantidad_carrito($usuario_id, $producto_id)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "UPDATE carrito SET cantidad = cantidad + 1 WHERE usuario_id = ? AND producto_id = ?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$usuario_id, $producto_id]);

        return ["mensaje" => "Cantidad incrementada correctamente"];
    } catch (PDOException $e) {
        return ["error" => "Error al incrementar la cantidad: " . $e->getMessage()];
    }
}

function decrementar_cantidad_carrito($usuario_id, $producto_id)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        // Primero verificamos la cantidad actual
        $consulta_check = "SELECT cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ?";
        $sentencia_check = $conexion->prepare($consulta_check);
        $sentencia_check->execute([$usuario_id, $producto_id]);
        
        if ($sentencia_check->rowCount() > 0) {
            $resultado = $sentencia_check->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado['cantidad'] <= 1) {
                // Si la cantidad es 1 o menor, eliminamos el producto del carrito
                $consulta = "DELETE FROM carrito WHERE usuario_id = ? AND producto_id = ?";
                $sentencia = $conexion->prepare($consulta);
                $sentencia->execute([$usuario_id, $producto_id]);
                return ["mensaje" => "Producto eliminado del carrito"];
            } else {
                // Si la cantidad es mayor a 1, decrementamos
                $consulta = "UPDATE carrito SET cantidad = cantidad - 1 WHERE usuario_id = ? AND producto_id = ?";
                $sentencia = $conexion->prepare($consulta);
                $sentencia->execute([$usuario_id, $producto_id]);
                return ["mensaje" => "Cantidad decrementada correctamente"];
            }
        } else {
            return ["error" => "Producto no encontrado en el carrito"];
        }
    } catch (PDOException $e) {
        return ["error" => "Error al decrementar la cantidad: " . $e->getMessage()];
    }
}

function ver_puntos_usuario($id_usuario)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        $respuesta["error"] = "Imposible conectar:" . $e->getMessage();
        return $respuesta;
    }

    try {
        $consulta = "SELECT puntos FROM usuarios WHERE id_usuario=?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$id_usuario]);
    } catch (PDOException $e) {
        $respuesta["error"] = "Imposible realizar la consulta:" . $e->getMessage();
        $sentencia = null;
        $conexion = null;
        return $respuesta;
    }

    if ($sentencia->rowCount() > 0) {
        $respuesta["puntos"] = $sentencia->fetch(PDO::FETCH_ASSOC);
    } else {
        $respuesta["mensaje"] = "Usuario con ".$id_usuario." no encontrado";
    }

    $sentencia = null;
    $conexion = null;
    return $respuesta;
}

function actualizar_puntos_usuario($id_usuario, $puntos)
{
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }

    try {
        $consulta = "UPDATE usuarios 
                     SET puntos = ? 
                     WHERE id_usuario = ?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$puntos, $id_usuario]);
    } catch (PDOException $e) {
        $sentencia = null;
        $conexion = null;
        return ["error" => "Error al actualizar el usuario: " . $e->getMessage()];
    }

    $sentencia = null;
    $conexion = null;
    return ["mensaje" => "Puntos del suario actualizado correctamente"];
}

function procesar_carrito($id_usuario){
    try {
        $conexion = new PDO("mysql:host=" . SERVIDOR_BD . ";dbname=" . NOMBRE_BD, USUARIO_BD, CLAVE_BD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } catch (PDOException $e) {
        return ["error" => "Imposible conectar: " . $e->getMessage()];
    }
    
    try {
        $consulta = "UPDATE carrito SET estado = 'procesado' WHERE usuario_id = ?";
        $sentencia = $conexion->prepare($consulta);
        $sentencia->execute([$id_usuario]);
    } catch (PDOException $e) {
        $sentencia = null;
        $conexion = null;
        return ["error" => "Error al procesar el carrito: " . $e->getMessage()];
    }
    $sentencia = null;
    $conexion = null;
    return ["mensaje" => "Carrito procesado correctamente"];
}


function cerrar_sesion()
{
    session_destroy();
    return ["success" => true, "message" => "Sesión cerrada"];
}

?>
