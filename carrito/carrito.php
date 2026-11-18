
<?php
session_start();
require_once "../conexion.php";

if (!isset($_SESSION["carrito"])) {
    $_SESSION["carrito"] = [];
}

// Actualizar cantidades
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["actualizar"])) {
    foreach ($_POST["cantidades"] as $id => $cantidad) {
        $id = (int)$id;
        $cantidad = filter_var($cantidad, FILTER_VALIDATE_INT);

        if (!$cantidad || $cantidad < 1) {
            unset($_SESSION["carrito"][$id]);
            continue;
        }

        $stmt = $conexion->prepare(
            "SELECT stock FROM productos WHERE id_producto = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $producto = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($producto) {
            $_SESSION["carrito"][$id] = min($cantidad, (int)$producto["stock"]);
        } else {
            unset($_SESSION["carrito"][$id]);
        }
    }

    header("Location: carrito.php");
    exit;
}

// Eliminar producto
if (isset($_GET["eliminar"])) {
    $id = (int)$_GET["eliminar"];
    unset($_SESSION["carrito"][$id]);

    header("Location: carrito.php");
    exit;
}

$productos = [];
$total = 0;

foreach ($_SESSION["carrito"] as $id => $cantidad) {
    $stmt = $conexion->prepare(
        "SELECT * FROM productos WHERE id_producto = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $producto = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($producto) {
        $producto["cantidad"] = $cantidad;
        $producto["subtotal"] = $producto["precio"] * $cantidad;
        $total += $producto["subtotal"];
        $productos[] = $producto;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi carrito | BLANK CO.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php">BLANK CO.</a>
        <a href="../index.php" class="btn btn-outline-light btn-sm">
            Seguir comprando
        </a>
    </div>
</nav>

<main class="container my-5">
    <h1 class="mb-4">Mi carrito de compras</h1>

    <?php if (empty($productos)): ?>

        <div class="bg-white rounded shadow-sm p-5 text-center">
            <h4>Tu carrito está vacío</h4>
            <p class="text-muted">¡Descubre nuestras playeras!</p>
            <a href="../index.php" class="btn btn-dark">
                Ir al catálogo
            </a>
        </div>

    <?php else: ?>

        <form method="POST">

            <div class="table-responsive bg-white rounded shadow-sm p-3">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Color</th>
                            <th>Talla</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($producto["nombre"]); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($producto["color"]); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($producto["talla"]); ?>
                            </td>
                            <td>
                                $<?php echo number_format($producto["precio"], 2); ?>
                            </td>
                            <td>
                                <input
                                    type="number"
                                    name="cantidades[<?php echo $producto["id_producto"]; ?>]"
                                    value="<?php echo $producto["cantidad"]; ?>"
                                    min="1"
                                    max="<?php echo $producto["stock"]; ?>"
                                    class="form-control"
                                    style="width:85px"
                                >
                            </td>
                            <td>
                                $<?php echo number_format($producto["subtotal"], 2); ?>
                            </td>
                            <td>
                                <a
                                    href="carrito.php?eliminar=<?php echo $producto["id_producto"]; ?>"
                                    class="btn btn-outline-danger btn-sm">
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    </tbody>
                </table>
            </div>

            <div class="text-end mt-4">
                <button type="submit" name="actualizar" class="btn btn-outline-dark">
                    Actualizar cantidades
                </button>

                <h3 class="mt-4">
                    Total: $<?php echo number_format($total, 2); ?>
                </h3>

                <a href="../pedidos/confirmar.php" class="btn btn-dark btn-lg mt-2">
                    Continuar compra
                </a>
            </div>

        </form>

    <?php endif; ?>
</main>

</body>
</html>
