<?php
session_start();

// Variable para mensajes hacia el usuario
$msj = '';

$opcion = $_POST["submit"] ?? '';

// evitar que accedan personas sin logear en el sitio
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}

switch ($opcion) {
    case 'Logout':
        session_destroy();
        header('Location: index.php');
        exit();
        break;
    case 'Productos':
        unset($_SESSION['tabla']);
            $_SESSION['tabla'] = $_SESSION['tabla'] ?? 'producto';
            header("location: listado.php");
        break;
    case 'Tiendas':
        unset($_SESSION['tabla']);
            $_SESSION['tabla'] = $_SESSION['tabla'] ?? 'tienda';
            header("location: listado.php");
        break;
    case 'Usuarios':
        unset($_SESSION['tabla']);
            $_SESSION['tabla'] = $_SESSION['tabla'] ?? 'usuarios';
            header("location: listado.php");
        break;
    case 'Stock':
        unset($_SESSION['tabla']);
            $_SESSION['tabla'] = $_SESSION['tabla']?? 'stock';
            header("location: listado.php");
        break;
    case 'Familia':
        unset($_SESSION['tabla']);
            $_SESSION['tabla'] = $_SESSION['tabla']?? 'familia';
            header("location: listado.php");
        break;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF - 8">
    <meta name="viewport" content="width=device - width, initial - scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="contenedor superior">
        <h1>Admin Panel</h1>
        <div>
            <form method="post" action="sitio.php">
                Conectado como <strong class="margen-dere"><?= $_SESSION['usuario'] ?></strong> <input class="btn" type="submit" value="Logout" name="submit">
            </form>
        </div>
    </div>


    <!-- Navigation Buttons -->
    <div class="contenedor">
        <form action="sitio.php" method="post" class="contenedor botones sitio">
            <input class="btn azul" type="submit" value="Productos" name="submit">
            <input class="btn verde" type="submit" value="Tiendas" name="submit">
            <input class="btn naranja" type="submit" value="Usuarios" name="submit">
            <input class="btn violeta" type="submit" value="Stock" name="submit">
            <input class="btn rojo" type="submit" value="Familia" name="submit">

        </form>

    </div>

    <!-- Placeholder for Future Content -->
    <div id="content" class="contenedor">
        <p class="seleccione">Selecciona una opción para gestionar los elementos de la tienda.</p>
    </div>
</body>

</html>