
<?php
session_start();
require_once "../conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $password = $_POST["password"] ?? "";
    $telefono = trim($_POST["telefono"] ?? "");
    $direccion = trim($_POST["direccion"] ?? "");

    if ($nombre === "" || !filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        $mensaje = "Verifica tus datos. La contraseña debe tener al menos 6 caracteres.";
    } else {

        $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $existe = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($existe) {
            $mensaje = "Este correo ya está registrado.";
        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conexion->prepare(
                "INSERT INTO usuarios (nombre, correo, password, telefono, direccion)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param("sssss", $nombre, $correo, $hash, $telefono, $direccion);

            if ($stmt->execute()) {
                $_SESSION["usuario_id"] = $stmt->insert_id;
                $_SESSION["usuario_nombre"] = $nombre;

                header("Location: ../index.php");
                exit;
            } else {
                $mensaje = "No fue posible registrar tu cuenta.";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta | BLANK CO.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <h2 class="text-center fw-bold mb-2">BLANK CO.</h2>
                    <p class="text-center text-muted mb-4">Crear una cuenta</p>

                    <?php if ($mensaje): ?>
                        <div class="alert alert-warning">
                            <?php echo htmlspecialchars($mensaje); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">

                        <label class="form-label">Nombre completo</label>
                        <input type="text" name="nombre" class="form-control mb-3" required>

                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="correo" class="form-control mb-3" required>

                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control mb-3" minlength="6" required>

                        <label class="form-label">Teléfono</label>
                        <input type="tel" name="telefono" class="form-control mb-3">

                        <label class="form-label">Dirección</label>
                        <textarea name="direccion" class="form-control mb-3"></textarea>

                        <button type="submit" class="btn btn-dark w-100">
                            Crear cuenta
                        </button>

                    </form>

                    <p class="text-center mt-3 mb-0">
                        ¿Ya tienes cuenta?
                        <a href="login.php">Inicia sesión</a>
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
