<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
session_unset(); // Elimina todas las variables de sesión
session_destroy(); // Destruye la sesión

// Redirige al usuario a la página principal
header("Location: index.php");
exit();
?>
