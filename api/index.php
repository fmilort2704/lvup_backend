<?php

// CORS headers SIEMPRE antes de cualquier salida
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Manejar preflight OPTIONS y terminar la petición
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require "src/funciones_servicios.php";
require __DIR__ . '/Slim/autoload.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

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

$app->post('/crear_producto_segunda_mano', function ($request) {
    $nombre = $request->getParam("nombre");
    $descripcion = $request->getParam("descripcion");
    $precio = $request->getParam("precio");
    $imagen_url = $request->getParam("imagen_url");
    $verificado = $request->getParam("verificado");
    $categoria_id = $request->getParam("categoria_id");
    $vendedor_id = $request->getParam("vendedor_id");
    $descripcion_larga = $request->getParam("descripcion_larga");
    
    echo json_encode(crear_producto_segunda_mano($nombre, $descripcion, $precio, $imagen_url, $verificado, $categoria_id, $vendedor_id, $descripcion_larga));
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
    $nombre = $request->getParam("nombre");
    $email = $request->getParam("email");
    $contrasenya = $request->getParam("contrasenya");
    echo json_encode(registrar_usuario($nombre, $email, $contrasenya));
});

$app->post('/login', function ($request) {
    //$usuario = $request->getParam("usuario");
    //$clave = $request->getParam("clave");
    $email = $request->getParam("email");
    $contrasenya = $request->getParam("contrasenya");
    echo json_encode(login($email, $contrasenya));
});

$app->get('/usuario/{id_usuario}', function ($request) {
    $id_usuario = $request->getAttribute("id_usuario");
    echo json_encode(obtener_usuario($id_usuario));
});

$app->put('/actualizar_usuario/{id_usuario}', function ($request) {
    $id_usuario = $request->getAttribute("id_usuario");

    $input = json_decode($request->getBody(), true);

    $nombre = isset($input["nombre"]) ? $input["nombre"] : null;
    $email = isset($input["email"]) ? $input["email"] : null;
    $contrasenya = isset($input["contrasenya"]) ? $input["contrasenya"] : null;

    echo json_encode(actualizar_usuario($id_usuario, $nombre, $email, $contrasenya));
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
    $titulo = $request->getParam("titulo");
    $descripcion = $request->getParam("descripcion");
    $comentario = $request->getParam("comentario");
    $imagen_url = $request->getParam("imagen_url");
    $autor_id = $request->getParam("autor_id");

    echo json_encode(crear_post($titulo, $descripcion, $comentario, $imagen_url, $autor_id));
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
    $contenido = $request->getParam("contenido");
    $post_id = $request->getParam("post_id");
    $autor_id = $request->getParam("autor_id");

    echo json_encode(crear_comentario($contenido, $post_id, $autor_id));
});

$app->delete('/eliminar_comentario/{id_comentario}', function ($request) {
    $id_comentario = $request->getAttribute("id_comentario");
    echo json_encode(eliminar_comentario($id_comentario));
});

$app->post('/introducir_carrito', function ($request) {
    $usuario_id = $request->getParam("usuario_id");
    $producto_id = $request->getParam("producto_id");
    echo json_encode(aniadir_al_carrito($usuario_id, $producto_id));
});

$app->get('/obtener_productos_carrito/{id_usuario}', function ($request) {
    $id_usuario = $request->getAttribute("id_usuario");
    echo json_encode(obtener_productos_carrito($id_usuario));
});

$app->delete('/eliminar_producto_carrito/{id_producto}', function ($request) {
    $id_producto = $request->getAttribute("id_producto");
    echo json_encode(eliminar_producto_carrito($id_producto));
});

$app->put('/incrementar_carrito', function ($request) {
    $usuario_id = $request->getParam("usuario_id");
    $producto_id = $request->getParam("producto_id");
    echo json_encode(incrementar_cantidad_carrito($usuario_id, $producto_id));
});

$app->put('/decrementar_carrito', function ($request) {
    $usuario_id = $request->getParam("usuario_id");
    $producto_id = $request->getParam("producto_id");
    echo json_encode(decrementar_cantidad_carrito($usuario_id, $producto_id));
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
/*
$app->post('/enviar_recibo', function($request){
    require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/src/SMTP.php';
    require_once __DIR__ . '/PHPMailer/src/Exception.php';
    
    $data = json_decode(file_get_contents('php://input'), true);
    $nombre = $data['nombre'] ?? '';
    $apellido = $data['apellido'] ?? '';
    $email = $data['email'] ?? '';
    $direccion = $data['direccion'] ?? '';
    $codigoPostal = $data['codigoPostal'] ?? '';
    $provincia = $data['provincia'] ?? '';
    $telefono = $data['telefono'] ?? '';
    $dni = $data['dni'] ?? '';
    $metodoPago = $data['metodoPago'] ?? '';
    $numeroTarjeta = $data['numeroTarjeta'] ?? '';
    $importe = '99,99€';

    $asunto = "Recibo de tu compra LvUp";
    $mensaje = "Hola $nombre $apellido,\n\nGracias por tu compra. Este es tu recibo:\n\n"
        . "Importe: $importe\n"
        . "Método de pago: $metodoPago\n"
        . ($numeroTarjeta ? "Número de tarjeta: $numeroTarjeta\n" : "")
        . "Dirección: $direccion\n"
        . "Código Postal: $codigoPostal\n"
        . "Provincia: $provincia\n"
        . "Teléfono: $telefono\n"
        . "DNI: $dni\n\n"
        . "¡Gracias por confiar en LvUp!";

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Cambia esto si usas otro proveedor
        $mail->SMTPAuth = true;
        $mail->Username = 'tucorreo@gmail.com'; // Tu correo
        $mail->Password = 'tu_contraseña';      // Tu contraseña o app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Configuración del correo
        $mail->setFrom('noreplyLvUp@gmail.com', 'LvUp');
        $mail->addAddress($email, "$nombre $apellido");
        $mail->Subject = $asunto;
        $mail->Body = $mensaje;

        $mail->send();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $mail->ErrorInfo]);
    }
});*/


$app->put('/procesar_carrito/{id_usuario}', function ($request, $response) {
    $id_usuario = $request->getAttribute("id_usuario");

    echo json_encode(procesar_carrito($id_usuario));
});

$app->run();
?>
