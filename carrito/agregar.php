
<?php
session_start();
require_once "../conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

$id_producto = filter_input(INPUT_POST, "id_producto", FILTER_VALIDATE_INT);
$cantidad = filter_input(INPUT_POST, "cantidad", FILTER_VALIDATE_INT);

if (!$id_producto || !$cantidad || $cantidad < 1) {
    header("Location: ../index.php");
    exit;
}

$sql = "SELECT id_producto, nombre, color, talla, precio, stock
        FROM productos WHERE id_producto = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_producto);
$stmt->execute();

$resultado = $stmt->get_result();
$producto = $resultado->fetch_assoc();

if (!$producto || $producto["stock"] < 1) {
    header("Location: ../index.php");
    exit;
}

if (!isset($_SESSION["carrito"])) {
    $_SESSION["carrito"] = [];
}

$cantidad_actual = $_SESSION["carrito"][$id_producto] ?? 0;
$nueva_cantidad = $cantidad_actual + $cantidad;

if ($nueva_cantidad > $producto["stock"]) {
    $nueva_cantidad = $producto["stock"];
}

$_SESSION["carrito"][$id_producto] = $nueva_cantidad;

$stmt->close();

header("Location: carrito.php");
exit;
?>
