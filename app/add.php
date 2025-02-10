<?php
session_start();

require_once __DIR__ . '/vendor/autoload.php';

use App\Crud\DB;

// Variable para mensajes hacia el usuario
$msj = '';

$opcion = $_POST["submit"] ?? '';

// evitar que accedan personas sin logear en el sitio
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}

// El formulario para añadir:
$formulario = prepararFormulario($_SESSION['tabla']);

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
    case 'Añadir':
        $DB = new DB();

        switch ($_SESSION['tabla']) {
            case 'usuarios':

                // aquí copié y pegué lo del index.
                $name = $_POST['nombre'];
                $password = $_POST['pass'];

                // Valido que los campos no estén sin rellenar
                if (empty(trim($name)) || empty(trim($password))) { //como repito este trozo tanto en LOGIN como en REGISTER podría trasladarlo fuera si voy bien de tiempo.
                    if (empty($name) && !empty($password)) {
                        $msj = 'El campo nombre está sin rellenar';
                    }
                    if (empty($password) && !empty($name)) {
                        $msj = 'El campo contraseña está sin rellenar';
                    }
                    if (empty($password) && empty($name)) {
                        $msj = 'Debe rellenar todos los campos';
                    }
                } else {
                    if ($DB->registrar_usuario($name, $password)) { //si todo está bien lo introducimos nos vamos de aquí.
                        $msj = 'Usuario añadido correctamente.';
                    } else { // si se repite el usuario no entra.
                        $msj = 'El usuario ya existe.';
                    }
                }

                break;
            case 'producto':

                $clave = $_POST['clave'];
                $nombre = $_POST['nombre'];
                $descripcion = $_POST['descripcion'];
                $pvp = $_POST['pvp'];
                $familia = $_POST['familia'];
                if(empty($clave) || empty($nombre) || empty($descripcion) || empty($pvp) || empty($familia)){
                    $msj = "Debes rellenar todos los campos.";
                } else {
                    // como ningun producto de la tabla tenía nombre, sino nombre_corto, he decidido omitirlo.
                    $arrayDatos = ["clave"=> $clave,
                                    "nombre_corto"=> $nombre,
                                    "descripcion"=> $descripcion,
                                    "pvp"=> $pvp,
                                    "familia"=> $familia];
                    $DB->add_fila('producto', $arrayDatos);
                    $msj = 'Producto añadido correctamente.';
                }


                break;
            case 'tienda':
                $nombre = $_POST['nombre'];
                $tlf = $_POST['telefono'];
                if(empty($nombre)){
                    $msj = "No puedes dejar sin nombre a la nueva tienda.";
                } else {
                    $arrayDatos = ["nombre"=> $nombre,
                                    "tlf" => $tlf];
                    $DB->add_fila('tienda', $arrayDatos);
                    $msj = 'Tienda añadida correctamente.';
                }
                break;
            case 'stock':

                $producto = $_POST['producto'];
                $tienda  = $_POST['tienda'];
                $unidades = $_POST['unidades'];

                if(empty($producto) || empty($tienda) || empty($unidades)){
                    $msj = "Debes rellenar todos los campos.";
                } else {
                    $arrayDatos = ["producto"=> $producto,
                                    "tienda" => $tienda,
                                    "unidades" => $unidades];
                    $DB->add_fila('stock', $arrayDatos);
                    $msj = 'Producto añadido correctamente.';
                }
                

                break;
            case 'familia':
                $cod = trim($_POST['cod']);
                $nombre = trim($_POST['nombre']);
                if(empty($cod) || empty($nombre)){
                    $msj = "Debes rellenar todos los campos.";
                } else {
                    $arrayDatos = ["cod"=> $cod,
                                    "nombre" => $nombre];
                    $DB->add_fila('familia', $arrayDatos);
                    $msj = 'Familia añadida correctamente.';
                }
                
                break;
        }

        break;
}

// preparo un formulario u otro dependiendo de la tabla en la que quiera insertar.
function prepararFormulario($tabla)
{
    $html = '';

    switch ($tabla) {
        case 'usuarios':
            $html .= '<div><label>Nombre </label> <input type="text" name="nombre"></div>';
            $html .= '<div><label>Contraseña </label> <input type="password" name="pass"></div>';
            break;
        case 'producto':
            $html .= '<div><label>Clave </label> <input type="text" name="clave" maxlength="9" required></div>';
            $html .= '<div><label>Nombre </label> <input type="text" name="nombre"></div>';
            $html .= '<div><label>Descripción </label> <input type="text" name="descripcion"></div>';
            $html .= '<div><label>PVP </label> <input type="number" name="pvp"></div>';

            $html .= '<div> Familia <select name="familia">';
            $DB = new DB();
            $familia = $DB->get_filas('familia');
            foreach ($familia as $fam) { // genero un select dinámicamente
                $html .= '<option value="' . $fam['cod'] . '">' . $fam['nombre'] . '</option>';
            }
            $html .= '</select></div>';


            break;
        case 'tienda':
            $html .= '<div><label>Nombre </label> <input type="text" name="nombre"></div>';
            $html .= '<div><label>Teléfono </label> <input type="number" name="telefono" minlength="9" maxlength="9"></div>';

            break;
        case 'stock':
            $DB = new DB();

            $productos = $DB->get_filas('producto');
            $html .= '<div> Producto <select name="producto">';
            foreach ($productos as $producto) {
                $html .= '<option value="' . $producto['cod'] . '">' . $producto['nombre_corto'] . '</option>';
            }
            $html .= '</select></div>';



            $tiendas = $DB->get_filas('tienda');
            $html .= '<div> Tienda <select name="tienda">';
            foreach ($tiendas as $tienda) {
                $html .= '<option value="' . $tienda['cod'] . '">' . $tienda['nombre'] . '</option>';
            }
            $html .= '</select></div>';

            $html .= '<div><label>Unidades </label> <input type="number" name="unidades"></div>';
            break;
        case 'familia':
            $html .= '<div><label>Tipo </label> <input type="text" name="cod"></div>';
            $html .= '<div><label>Nombre </label> <input type="text" name="nombre"></div>';
            break;
    }



    return $html;
}


?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Añadir</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="contenedor superior">
        <h1>Add</h1>
        <div>
            <form method="post" action="sitio.php">
                Conectado como <strong class="margen-dere"><?= $_SESSION['usuario'] ?></strong> <input class="btn" type="submit" value="Logout" name="submit">
                <input class="btn azul" type="submit" value="Volver" name="submit">
            </form>
        </div>
    </div>

    <div class="añadir">
        <form class="" action="add.php" method="post">

            <?= $formulario ?? 'No se ha encontrado el formulario' ?>

            <div class="btn-añadir">
                <input type="submit" class="btn" value="Añadir" name="submit">
            </div>

        </form>

        
    </div>
    <!-- MENSAJES PARA EL USUARIO -->
    <div class="contenedor">
    <h2 class=" importante"><?= $msj?? ''?></h2>

    </div>


</body>

</html>