<?php
include 'db.php';
include 'functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function getStatusColor($status) {
    switch ($status) {
        case 'Entregado':
            return 'green';
        case 'En camino':
            return 'orange';
        case 'En preparación':
            return 'red';
        default:
            return 'black';
    }
}

if (isset($_SESSION['username'])) {
    $userId = getUserIdByUsername($conn, $_SESSION['username']);
    $orders = getOrdersByUserId($conn, $userId);
    
    if ($orders->num_rows > 0): ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="Styles\styles.css">
            <title>Mis Pedidos</title>
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
            <div class="container">
                <div class="orders">
                    <h2>Mis Pedidos</h2>
                    <ul>
                        <?php while ($order = $orders->fetch_assoc()): ?>
                            <li>
                                <h3>Pedido #<?php echo $order['id']; ?></h3>
                                <p>Total: $<?php echo $order['total']; ?></p>
                                <p>Estado: <span class="order-status" style="color: <?php echo getStatusColor($order['status']); ?>"><?php echo $order['status']; ?></span></p>
                                <ul>
                                    <?php
                                    $orderItems = getOrderItems($conn, $order['id']);
                                    while ($item = $orderItems->fetch_assoc()):
                                        $product = getProductById($conn, $item['product_id']);
                                    ?>
                                        <li><?php echo $product['name']; ?> - $<?php echo $item['price']; ?> x <?php echo $item['quantity']; ?></li>
                                    <?php endwhile; ?>
                                </ul>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </body>
        </html>
    <?php else: ?>
        <script>
            alert('No tienes pedidos realizados.');
            window.location.href = 'index.php';
        </script>
    <?php endif;
}
?>
