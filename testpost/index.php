<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "¡POST recibido!";
} else {
    echo "No es POST, es: " . $_SERVER['REQUEST_METHOD'];
}
