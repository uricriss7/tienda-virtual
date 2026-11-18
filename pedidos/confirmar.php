
<?php
session_start();
require_once "../conexion.php";

// Verificar que el usuario inició sesión
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../usuarios/login.php");
    exit;
}

// Verificar que el carrito tenga productos
if (empty($_SESSION["carrito"])) {
    header("Location: ../carrito/carrito.php");
    exit;
}

$usuario_id = (int)$_SESSION["usuario_id"];
$carrito = $_SESSION["carrito"];

try {

    $conexion->begin_transaction();

    $productos = [];
    $total = 0;

    // Consultar productos y bloquear sus existencias
    foreach ($carrito as $id => $cantidad) {

        $id = (int)$id;
        $cantidad = (int)$cantidad;

        $stmt = $conexion->prepare(
            "SELECT id_producto, nombre, color, talla, precio, stock
             FROM productos
             WHERE id_producto = ?
             FOR UPDATE"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $producto = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$producto || $cantidad < 1 || $cantidad > $producto["stock"]) {
            throw new Exception("No hay existencias suficientes de un producto.");
        }

        $producto["cantidad"] = $cantidad;
        $productos[] = $producto;

        $total += $producto["precio"] * $cantidad;
    }

    // Registrar el pedido
    $stmt = $conexion->prepare(
        "INSERT INTO pedidos (id_usuario, total, estado)
         VALUES (?, ?, 'Pendiente')"
    );

    $stmt->bind_param("id", $usuario_id, $total);
    $stmt->execute();

    $id_pedido = $stmt->insert_id;
    $stmt->close();

    // Registrar cada producto comprado
    foreach ($productos as $producto) {

        $stmt = $conexion->prepare(
            "INSERT INTO detalle_pedido
             (id_pedido, id_producto, cantidad, precio_unitario)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "iiid",
            $id_pedido,
            $producto["id_producto"],
            $producto["cantidad"],
            $producto["precio"]
        );

        $stmt->execute();
        $stmt->close();

        // Descontar existencias
        $stmt = $conexion->prepare(
            "UPDATE productos
             SET stock = stock - ?
             WHERE id_producto = ? AND stock >= ?"
        );

        $stmt->bind_param(
            "iii",
            $producto["cantidad"],
            $producto["id_producto"],
            $producto["cantidad"]
        );

        $stmt->execute();

        if ($stmt->affected_rows !== 1) {
            throw new Exception("No fue posible actualizar el inventario.");
        }

        $stmt->close();
    }

    $conexion->commit();

    // Vaciar el carrito
    unset($_SESSION["carrito"]);

    $compra_exitosa = true;

} catch (Exception $e) {

    $conexion->rollback();
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación | BLANK CO.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow-sm border-0 mx-auto" style="max-width:600px;">
        <div class="card-body p-5 text-center">

            <?php if (!empty($compra_exitosa)): ?>

                <h1 class="fw-bold">¡Gracias por tu compra!</h1>

                <p class="text-muted mt-3">
                    Tu pedido ha sido registrado correctamente.
                </p>

                <h4 class="my-4">
                    Pedido número: #<?php echo $id_pedido; ?>
                </h4>

                <h3>
                    Total: $<?php echo number_format($total, 2); ?>
                </h3>

                <p class="text-muted">
                    Estado: Pendiente
                </p>

                <a href="../index.php" class="btn btn-dark mt-3">
                    Volver a la tienda
                </a>

            <?php else: ?>

                <h3>No se pudo completar tu compra</h3>

                <p class="text-danger">
                    <?php echo htmlspecialchars($error ?? "Ocurrió un error."); ?>
                </p>

                <a href="../carrito/carrito.php" class="btn btn-dark">
                    Volver al carrito
                </a>

            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>
