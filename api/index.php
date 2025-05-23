<?php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

require "src/funciones_servicios.php";
require __DIR__ . '/Slim/autoload.php';

$app = new \Slim\App;


$app->get('/obtener_productos', function () {
    echo json_encode(obtener_productos());
});

$app->get('/obtener_producto/{id_producto}', function ($request) {
    $id_producto = $request->getAttribute("id_producto");
    echo json_encode(obtener_producto_por_id($id_producto));
});

$app->post('/crear_producto', function ($request) {
    $productos = [
        "nombre" => "Mando PS5 DualSense",
        "descripcion" => "Mando inalámbrico original de Sony para PS5, color blanco",
        "estado" => "nuevo",
        "precio" => 69.99,
        "stock" => 10,
        "imagen_url" => "https://example.com/images/mando_ps5.jpg",
        "categoria_id" => 4,
        "vendedor_id" => 2
];
    echo json_encode(crear_producto($productos["nombre"], $productos["descripcion"], $productos["estado"], $productos["precio"], $productos["stock"], $productos["imagen_url"], $productos["categoria_id"], $productos["vendedor_id"]));
});

$app->delete('/borrar_producto/{id_producto}', function ($request) {
    $id_producto = $request->getAttribute("id_producto");
    echo json_encode(borrar_producto($id_producto));
});

$app->get('/obtener_productos_categoria/{id_categoria}', function ($request) {
    $id_categoria = $request->getAttribute("id_categoria");
    echo json_encode(obtener_productos_por_categoria($id_categoria));
});

$app->get('/obtener_productos_usuarios/{id_usuario}', function ($request) {
    $id_usuario = $request->getAttribute("id_usuario");
    echo json_encode(obtener_productos_por_usuario($id_usuario));
});

$app->post('/registrarse', function ($request) {
    $usuario = [
        "nombre" => "Ejemplo",
        "email" => "ejemplo@gmail.com",
        "contrasenya" => "123456",
];
    echo json_encode(registrar_usuario($usuario["nombre"], $usuario["email"], $usuario["contrasenya"]));
});

$app->post('/login', function ($request) {
    //$usuario = $request->getParam("usuario");
    //$clave = $request->getParam("clave");
    $usuario = [
        "email" => "ejemplo@gmail.com",
        "contrasenya" => "123456",
];
    echo json_encode(login($usuario["email"], $usuario["contrasenya"]));
});

$app->get('/usuario/{id_usuario}', function ($request) {
    $id_usuario = $request->getAttribute("id_usuario");
    echo json_encode(obtener_usuario($id_usuario));
});

$app->put('/actualizar_usuario/{id_usuario}', function ($request) {
    $id_usuario = $request->getAttribute("id_usuario");
    $usuario = [
        "nombre" => "Ejemplo",
        "email" => "ejemplo@gmail.com",
        "contrasenya" => "123456",
        "rol" => "user",
        "puntos" => 100,
        "verificado" => 1,
];
    
    echo json_encode(actualizar_usuario($id_usuario, $usuario["nombre"], $usuario["email"], $usuario["contrasenya"], $usuario["rol"], $usuario["puntos"], $usuario["verificado"]));
});


$app->delete('/eliminar_usuario/{id_usuario}', function ($request) {
    $id_usuario = $request->getAttribute("id_usuario");
    echo json_encode(eliminar_usuario($id_usuario));
});

$app->get('/obtener_posts', function () {
    echo json_encode(obtener_posts());
});

$app->get('/obtener_post/{id_post}', function ($request) {
    $id_post = $request->getAttribute("id_post");
    echo json_encode(obtener_post($id_post));
});

$app->post('/crear_post', function ($request) {
    $post = [
        "titulo" => "Ejemplo",
        "comentario" => "texto de ejemplo",
        "autor_id" => 1,
];
    echo json_encode(crear_post($post["titulo"], $post["comentario"], $post["autor_id"]));
});

$app->put('/actualizar_post/{id_post}', function ($request) {
    $id_post = $request->getAttribute("id_post");
    $post = [
        "titulo" => "Ejemplo epico",
        "comentario" => "texto de ejemplo epico",
        "autor_id" => 3,
];
    echo json_encode(actualizar_post($id_post, $post["titulo"], $post["comentario"], $post["autor_id"]));
});

$app->delete('/eliminar_post/{id_post}', function ($request) {
    $id_post = $request->getAttribute("id_post");
    echo json_encode(eliminar_post($id_post));
});

$app->get('/obtener_post_por_usuario/{id_usuario}', function ($request) {
    $id_usuario = $request->getAttribute("id_usuario");
    echo json_encode(obtener_post_por_usuario($id_usuario));
});

$app->get('/obtener_comentario_de_post/{id_post}', function ($request) {
    $id_post = $request->getAttribute("id_post");
    echo json_encode(obtener_comentario_de_post($id_post));
});

$app->post('/crear_comentario', function ($request) {
    $comentario = [
        "contenido" => "Ejemplode comentario",
        "post_id" => 3,
        "autor_id" => 6,
];
    echo json_encode(crear_comentario($comentario["contenido"], $comentario["post_id"], $comentario["autor_id"]));
});

$app->delete('/eliminar_comentario/{id_comentario}', function ($request) {
    $id_comentario = $request->getAttribute("id_comentario");
    echo json_encode(eliminar_comentario($id_comentario));
});

$app->post('/introducir_carrito', function ($request) {
    $carrito = [
        "usuario_id" => 1,
        "producto_id" => 4,
        "cantidad" => 2,
];
    echo json_encode(aniadir_al_carrito($carrito["usuario_id"], $carrito["producto_id"], $carrito["cantidad"]));
});

$app->get('/obtener_productos_carrito/{id_usuario}', function ($request) {
    $id_usuario = $request->getAttribute("id_usuario");
    echo json_encode(obtener_productos_carrito($id_usuario));
});

$app->delete('/eliminar_producto_carrito/{id_producto}', function ($request) {
    $id_producto = $request->getAttribute("id_producto");
    echo json_encode(eliminar_producto_carrito($id_producto));
});

$app->get('/ver_puntos_usuario/{id_usuario}', function ($request) {
    $id_usuario = $request->getAttribute("id_usuario");
    echo json_encode(ver_puntos_usuario($id_usuario));
});

$app->put('/actualizar_puntos_usuario/{id_usuario}', function ($request) {
    $id_usuario = $request->getAttribute("id_usuario");
    $usuario = [
        "puntos" => 100,
];
    
    echo json_encode(actualizar_puntos_usuario($id_usuario, $usuario["puntos"]));
});


$app->get('/logueado', function () {
    $test = validateToken();
    if (is_array($test)) {
        echo json_encode($test);
    } else {
        echo json_encode(array("no_auth" => "No tienes permisos para usar este servicio"));
    }
});

$app->get('/cerrarSesion', function () {
    echo json_encode(cerrar_sesion());
});

$app->run();
?>
