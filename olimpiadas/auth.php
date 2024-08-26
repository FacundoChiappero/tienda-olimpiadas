<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
} // Asegúrate de que session_start() se llama solo una vez

include 'db.php';
include 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // Validar y sanitizar datos
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            $error = "El nombre de usuario contiene caracteres inválidos";
        } elseif (strlen($password) < 5) {
            $error = "La contraseña debe tener al menos 6 caracteres";
        } else {
            // Consulta preparada
            $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($hashed_password);
                $stmt->fetch();

                if (password_verify($password, $hashed_password)) {
                    $_SESSION['username'] = $username;
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Usuario o contraseña incorrectos";
                }
            } else {
                $error = "Usuario o contraseña incorrectos";
            }
            $stmt->close();
        }
    } elseif (isset($_POST['register'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);

        // Validar y sanitizar datos
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            $error = "El nombre de usuario contiene caracteres inválidos";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Correo electrónico inválido";
        } elseif (strlen($password) < 5) {
            $error = "La contraseña debe tener al menos 6 caracteres";
        } elseif ($password !== $confirm_password) {
            $error = "Las contraseñas no coinciden";
        } else {
            // Consulta preparada para verificar existencia
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "El usuario o correo electrónico ya están registrados";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Consulta preparada para insertar nuevo usuario
                $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $email, $hashed_password);

                if ($stmt->execute()) {
                    $success = "Registro exitoso. Por favor, inicie sesión.";
                } else {
                    $error = "Error al registrar el usuario";
                }
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login/Registro</title>
    <link rel="stylesheet" href="Styles\log.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>
<body>
<nav>
    <a href="index.php" class="logo">Tienda olimpiadas</a>
    <a href="index.php" class="active"><i class="fas fa-home"></i>Inicio</a>
    <?php if (isset($_SESSION['username'])): ?>
        <a href="checkout.php"><i class="fas fa-shopping-cart"></i>Carrito</a>
        <a href="orders.php"><i class="fas fa-box"></i>Mis Pedidos</a>
        <?php if ($_SESSION['username'] === 'admin'): ?>
            <a href="admin_orders.php"><i class="fas fa-box"></i>Administrar Pedidos</a>
        <?php endif; ?>
        <a href="logout.php" class="right">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
    <?php else: ?>
        <a href="auth.php" class="right">Login/Registro</a>
    <?php endif; ?>
</nav>
<div class="container login-register">
    <div class="register">
        <form method="POST" action="">
            <h2>Registrarse</h2>
            <input type="text" name="username" placeholder="Usuario" required>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <input type="password" name="confirm_password" placeholder="Repetir contraseña" required>
            <button type="submit" name="register">Registrarse</button>
        </form>
    </div>
    
    <div class="v-line"></div>
    
    <div class="login">
        <form method="POST" action="">
            <h2>Iniciar Sesión</h2>
            <input type="text" name="username" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit" name="login">Login</button>
        </form>
    </div>
</div>
<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
</body>
</html>
