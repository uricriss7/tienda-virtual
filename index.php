
<?php
require_once "conexion.php";

$sql = "SELECT * FROM productos ORDER BY
    FIELD(color, 'Blanco', 'Negro', 'Azul marino', 'Gris', 'Beige'),
    FIELD(talla, 'CH', 'M', 'G', 'EG')";

$resultado = $conexion->query($sql);

$productos = [];

if ($resultado) {
    while ($producto = $resultado->fetch_assoc()) {
        $productos[$producto["color"]][] = $producto;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda de Playeras</title>
    <link rel="stylesheet" href="estilos/estilos.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">

        <a class="navbar-brand fw-bold" href="index.php">
            BLANK CO.
        </a>

        <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse"
            data-bs-target="#menuPrincipal"
            aria-controls="menuPrincipal"
            aria-expanded="false"
            aria-label="Abrir menú">

            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">

            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link active" href="index.php">Inicio</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#catalogo">Catálogo</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="carrito/carrito.php">Carrito</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="usuarios/login.php">Iniciar sesión</a>
                </li>

            </ul>

        </div>
    </div>
</nav>


<header class="text-center py-5 bg-light">
    <div class="container py-4">
        <p class="text-uppercase text-secondary fw-bold">
            Básicos que nunca pasan de moda
        </p>

        <h1 class="display-3 fw-bold">
            Viste simple. Viste diferente.
        </h1>

        <p class="lead mt-3">
            Playeras básicas, comodidad y estilo para todos los días.
        </p>

        <a href="#catalogo" class="btn btn-dark btn-lg mt-3">
            Explorar colección
        </a>
    </div>
</header>



    <main>
        
       
<section class="container my-5" id="catalogo">

    <h2 class="text-center mb-4">Nuestro catálogo</h2>

    <div class="row g-4 justify-content-center">

        <?php foreach ($productos as $color => $variantes): ?>

            <?php
            $imagenes = [
                "Blanco" => "playera-blanca.jpg",
                "Negro" => "playera-negra.jpg",
                "Azul marino" => "playera-azul-marino.jpg",
                "Gris" => "playera-gris.jpg",
                "Beige" => "playera-beige.jpg"
            ];

            $imagen = $imagenes[$color];
            ?>

            <div class="col-12 col-sm-6 col-lg-4">

                <div class="card h-100 shadow-sm">

                    <div class="d-flex justify-content-center align-items-center"
                         style="height:260px; padding:15px;">

                        <img
                            src="imagenes/productos/<?php echo $imagen; ?>"
                            alt="<?php echo htmlspecialchars($variantes[0]["nombre"]); ?>"
                            style="width:100%; height:100%; object-fit:contain;"
                        >

                    </div>

                    <div class="card-body">

                        <h5 class="card-title">
                            <?php echo htmlspecialchars($variantes[0]["nombre"]); ?>
                        </h5>

                        <h5 class="fw-bold">$199.00</h5>

                        <form action="carrito/agregar.php" method="POST">

                            <input type="hidden" name="color"
                                   value="<?php echo htmlspecialchars($color); ?>">

                            <label class="form-label mt-2">Selecciona tu talla</label>

                            <select name="id_producto" class="form-select mb-3" required>

                                <?php foreach ($variantes as $variante): ?>

                                    <option
                                        value="<?php echo $variante["id_producto"]; ?>"
                                        <?php echo $variante["stock"] <= 0 ? "disabled" : ""; ?>
                                    >
                                        <?php echo htmlspecialchars($variante["talla"]); ?>
                                        <?php echo $variante["stock"] <= 0 ? "(Agotada)" : "(Disponible)"; ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                            <label class="form-label">Cantidad</label>

                            <input
                                type="number"
                                name="cantidad"
                                class="form-control mb-3"
                                value="1"
                                min="1"
                                max="10"
                                required
                            >

                            <button type="submit" class="btn btn-dark w-100">
                                Agregar al carrito
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</section>

    <h2 class="text-center mb-4">Nuestro catálogo</h2>

    <div class="row g-4 justify-content-center">

        <?php if ($resultado && $resultado->num_rows > 0): ?>

            <?php while ($producto = $resultado->fetch_assoc()): ?>

                <div class="col-12 col-sm-6 col-lg-3">

                    <div class="card h-100 shadow-sm">
                        <?php
$imagenes = [
    "Blanco" => "playera-blanca.jpg",
    "Negro" => "playera-negra.jpg",
    "Azul marino" => "playera-azul-marino.jpg",
    "Gris" => "playera-gris.jpg",
    "Beige" => "playera-beige.jpg"
];

$imagen = $imagenes[$producto["color"]] ?? "playera-blanca.jpg";
?>

<div class="d-flex justify-content-center align-items-center"
     style="height: 260px; padding: 15px;">

    <img
        src="imagenes/productos/<?php echo $imagen; ?>"
        alt="<?php echo htmlspecialchars($producto["nombre"]); ?>"
        style="width: 100%; height: 100%; object-fit: contain; object-position: center;"
    >

</div>

                        <div class="card-body">

                            <h5 class="card-title">
                                <?php echo htmlspecialchars($producto["nombre"]); ?>
                            </h5>

                            <p class="card-text">
                                Color: <?php echo htmlspecialchars($producto["color"]); ?>
                            </p>

                            <p class="card-text">
                                Talla: <?php echo htmlspecialchars($producto["talla"]); ?>
                            </p>

                            <h5 class="fw-bold">
                                $<?php echo number_format($producto["precio"], 2); ?>
                            </h5>

                            <p class="text-muted">
                                Disponibles: <?php echo (int)$producto["stock"]; ?>
                            </p>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p class="text-center">No hay productos disponibles.</p>

        <?php endif; ?>

    </div>

</section>

    </main>

    <footer>
        <p>BLANK CO. | Todos los derechos reservados.</p>
    </footer>

</body>
</html>

