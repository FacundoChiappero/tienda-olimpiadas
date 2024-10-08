<?php
include 'db.php';
include 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION['username'] = $username;
    } else {
        echo "Usuario o contraseña incorrectos";
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'order') {
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $product = getProductById($conn, $item['id']);
            $userId = getUserIdByUsername($conn, $_SESSION['username']);
            $quantity = $item['quantity'];
            $totalPrice = $product['price'] * $quantity;

            $sql = "INSERT INTO orders (user_id, product_id, quantity, total_price) VALUES ('$userId', '{$product['id']}', '$quantity', '$totalPrice')";
            $conn->query($sql);
        }

        unset($_SESSION['cart']);
        echo "<script>alert('Pedido realizado con éxito');</script>";
    } else {
        echo "<script>alert('El carrito está vacío');</script>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
    if ($conn->query($sql) === TRUE) {
        echo "Registro exitoso. Por favor, inicie sesión.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'add') {
    addToCart($_GET['id']);
}

if (isset($_GET['action']) && $_GET['action'] == 'remove') {
    removeFromCart($_GET['id']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_action'])) {
    if ($_SESSION['username'] === 'admin') {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $image_url = $_POST['image_url'];

        $sql = "INSERT INTO products (name, price, image_url) VALUES ('$name', '$price', '$image_url')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Producto agregado exitosamente');</script>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "No tienes permisos para agregar productos.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_product'])) {
    if ($_SESSION['username'] === 'admin') {
        $id = intval($_POST['id']);

        $sql = "DELETE FROM products WHERE id = $id";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Producto eliminado exitosamente');</script>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "No tienes permisos para eliminar productos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Styles\styles.css">
    <title>Tienda olimpiadas</title>
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
        <a href="logout.php" class="right">Logout (<?php echo $_SESSION['username']; ?>)</a>
    <?php else: ?>
        <a href="auth.php" class="right">Login/Registro</a>
    <?php endif; ?>
</nav>
<?php if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin'): ?>
    <div class="admin-section">
        <h2>Agregar Producto</h2>
        <form method="POST" action="">
            <input type="text" name="name" placeholder="Nombre del Producto" required>
            <input type="text" name="price" placeholder="Precio" required>
            <input type="text" name="image_url" placeholder="URL de la Imagen" required>
            <button class="admin-section-button" type="submit" name="admin_action">Agregar Producto</button>
        </form>
        <br>
    </div>
<?php endif; ?>

<div class="products">
    <h2>Productos Disponibles</h2>
    <ul>
        <?php
        $products = getProducts($conn);
        while ($product = $products->fetch_assoc()) {
            echo "<li>";
            echo "<img src='" . $product['image_url'] . "' alt='" . $product['name'] . "'>";
            echo $product['name'] . " - $" . $product['price'];
            echo " <a href='index.php?action=add&id=" . $product['id'] . "'>Agregar al Carrito</a>";

            if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin') {
                echo " <form method='POST' action='' style='display:inline;'>";
                echo "<input type='hidden' name='id' value='" . $product['id'] . "'>";
                echo "<button class='admin-section-button' type='submit' name='delete_product' onclick=\"return confirm('¿Estás seguro de que quieres eliminar este producto?')\">Eliminar</button>";
                echo "</form>";
            }

            echo "</li>";
        }
        ?>
    </ul>
</div>
<div class="cart">
    <h2>Carrito de Compras</h2>
    <ul>
        <?php
        if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
            foreach ($_SESSION['cart'] as $item) {
                $product = getProductById($conn, $item['id']);
                echo "<li>" . $product['name'] . " - $" . $product['price'] . " x " . $item['quantity'];
                echo " <a href='index.php?action=remove&id=" . $product['id'] . "'>Eliminar uno</a></li>";
            }
            echo "<br>";
            echo "<br>";
        } else {
            echo "<li>El carrito está vacío</li>";
        }
        ?>
    </ul>
    <?php
    echo "<p>Total: $" . calculateCartTotal($conn) . "</p>"; ?>
    <br>
    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
        <a href="checkout.php" class="botoncompra">Comprar Carrito</a>
    <?php endif; ?>
</div>
</body>
</html>
