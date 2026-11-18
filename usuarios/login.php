
<?php
session_start();
require_once "../conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $correo = trim($_POST["correo"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $conexion->prepare(
        "SELECT id_usuario, nombre, password FROM usuarios WHERE correo = ?"
    );

    $stmt->bind_param("s", $correo);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();
    $stmt->close();

    if ($usuario && password_verify($password, $usuario["password"])) {

        session_regenerate_id(true);

        $_SESSION["usuario_id"] = $usuario["id_usuario"];
        $_SESSION["usuario_nombre"] = $usuario["nombre"];

        header("Location: ../index.php");
        exit;

    } else {
        $mensaje = "Correo o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | BLANK CO.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <h2 class="text-center fw-bold">BLANK CO.</h2>
                    <p class="text-center text-muted mb-4">Bienvenido de nuevo</p>

                    <?php if ($mensaje): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($mensaje); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">

                        <label class="form-label">Correo electrónico</label>
                        <input
                            type="email"
                            name="correo"
                            class="form-control mb-3"
                            required
                        >

                        <label class="form-label">Contraseña</label>
                        <input
                            type="password"
                            name="password"
                            class="form-control mb-3"
                            required
                        >

                        <button type="submit" class="btn btn-dark w-100">
                            Iniciar sesión
                        </button>

                    </form>

                    <p class="text-center mt-3 mb-0">
                        ¿No tienes cuenta?
                        <a href="registro.php">Regístrate aquí</a>
                    </p>

                </div>
            </div>

            <p class="text-center mt-3">
                <a href="../index.php" class="text-dark">Volver a la tienda</a>
            </p>

        </div>
    </div>
</div>

</body>
</html>
