<?php
session_start();

require_once __DIR__ . '/vendor/autoload.php';

use App\Crud\DB;

// evitar que accedan personas sin logear en el sitio
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}

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
    case 'Volver':

        unset($_SESSION['tabla']);
        header("location: sitio.php");
        exit();
        break;
    case 'X': //eliminar
        $cod = $_POST['cod'];
        $tabla = $_POST['tabla'];
        $DB = new DB();
        $msj = $DB->borrar_fila($cod, $tabla);
        break;
}


function crearTabla()
{
    $DB = new DB();

    $tablaHTML = "<table>";

    // primero los campos
    $campos = $DB->get_campos($_SESSION['tabla']);
    $tabla = $_SESSION['tabla'];

    foreach ($campos as $campo) {
        $tablaHTML .= "<th>" . $campo . "</th>";
    }

    // saco TODAS las filas dentro de un array de arrays
    $contenido = $DB->get_filas($_SESSION['tabla']);
    // saco uno a uno los arrays
    foreach ($contenido as $fila) {
        $tablaHTML .= "<tr>";

        // saco de cada array lo que tiene dentro
        foreach ($campos as $campo) {
            $tablaHTML .= "<td>" . $fila[$campo] . "</td>";
        }

        $tablaHTML .= "
            <td>
                <form method='post'>
                    <input type='hidden' name='cod' value='" . (int)$fila['cod'] . "'>
                    <input type='hidden' name='tabla' value='" . $tabla . "'>
                    <input type='submit' name='submit' value='X' data-text='X' class='btn'>
                </form>
            </td>
        ";
        $tablaHTML .= "</tr>";
    }


    $tablaHTML .= "</table>";
    return $tablaHTML;
}


?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Listado</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="contenedor superior">
        <h1>Listado del contenido de <?= $_SESSION['tabla']?></h1>
        <div>
            <form method="post" action="sitio.php">
                Conectado como <strong class="margen-dere"><?= $_SESSION['usuario'] ?></strong> <input class="btn" type="submit" value="Logout" name="submit">
                <input class="btn azul" type="submit" value="Volver" name="submit">
            </form>
        </div>
</div>

    <div class="contenedor">
    <form action="add.php" method="post">
        <input type="submit" class="btn-agregar" value="+ AÑADIR">
    </form>
</div>
    <div class="contenedor">
        <h2 class="importante"><?= $msj ?? '' ?></h2>
    </div>
    <?= crearTabla() ?? '' ?>

</body>

</html>